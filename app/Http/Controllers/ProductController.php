<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\RestockRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Créer un produit
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
        ]);

        return response()->json([
            'id' => $product->id,
            'user_id' => $product->user_id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'created_at' => $product->created_at->toISOString(),
        ], 201);
    }

    /**
     * Lister tous les produits
     */
    public function index(): JsonResponse
    {
        $products = Product::all(['id', 'name', 'price', 'stock', 'min_stock']);

        return response()->json($products);
    }

    /**
     * Créer une commande
     */
    public function createOrder(OrderRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $buyer = $request->user();
            $product = Product::findOrFail($request->product_id);

            // Vérifier le stock suffisant
            if (!$product->hasSufficientStock($request->quantity)) {
                return response()->json([
                    'error' => 'Stock insuffisant pour ce produit.'
                ], 400);
            }

            // Empêcher l'achat de son propre produit
            if ($product->user_id === $buyer->id) {
                return response()->json([
                    'error' => 'Vous ne pouvez pas acheter votre propre produit.'
                ], 400);
            }

            $totalPrice = $product->price * $request->quantity;

            // Créer la commande
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $product->user_id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'total_price' => $totalPrice,
                'status' => 'success',
            ]);

            // Décrémenter le stock
            $product->decrementStock($request->quantity);

            // Vérifier l'alerte de stock faible
            $lowStockAlert = $product->isLowStock();

            return response()->json([
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'low_stock_alert' => $lowStockAlert,
                'created_at' => $order->created_at->toISOString(),
            ], 201);
        });
    }

    /**
     * Réapprovisionner un produit
     */
    public function restock(RestockRequest $request, $id): JsonResponse
    {
        $product = Product::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $oldStock = $product->stock;
        $product->incrementStock($request->quantity);
        $newStock = $product->stock;

        $lowStockAlert = $product->isLowStock();

        return response()->json([
            'product_id' => $product->id,
            'new_stock' => $newStock,
            'restocked_quantity' => $request->quantity,
            'low_stock_alert' => $lowStockAlert,
        ]);
    }

    /**
     * Lister les commandes de l'utilisateur (en tant qu'acheteur ou vendeur)
     */
    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with(['product:id,name', 'buyer:id,name', 'seller:id,name'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'buyer_id', 'seller_id', 'product_id', 'quantity', 'total_price', 'status', 'created_at']);

        return response()->json($orders);
    }
}
