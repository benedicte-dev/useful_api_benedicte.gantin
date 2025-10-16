<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopupRequest;
use App\Http\Requests\TransferRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Obtenir le solde du portefeuille
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['balance' => 0.00]
        );

        return response()->json([
            'user_id' => $wallet->user_id,
            'balance' => (float) $wallet->balance,
        ]);
    }

    /**
     * Effectuer un transfert
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $sender = $request->user();
            $senderWallet = Wallet::firstOrCreate(
                ['user_id' => $sender->id],
                ['balance' => 0.00]
            );

            $receiver = User::findOrFail($request->receiver_id);
            $receiverWallet = Wallet::firstOrCreate(
                ['user_id' => $receiver->id],
                ['balance' => 0.00]
            );

            $amount = $request->amount;

            // Vérifier le solde suffisant
            if (!$senderWallet->hasSufficientBalance($amount)) {
                return response()->json([
                    'error' => 'Solde insuffisant.'
                ], 400);
            }

            // Effectuer le transfert
            $senderWallet->decrement('balance', $amount);
            $receiverWallet->increment('balance', $amount);

            // Enregistrer la transaction
            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'type' => 'transfer',
                'status' => 'success',
                'description' => 'Transfert d\'argent'
            ]);

            return response()->json([
                'transaction_id' => $transaction->id,
                'sender_id' => $transaction->sender_id,
                'receiver_id' => $transaction->receiver_id,
                'amount' => (float) $transaction->amount,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->toISOString(),
            ], 201);
        });
    }

    /**
     * Recharger le portefeuille
     */
    public function topup(TopupRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0.00]
            );

            $amount = $request->amount;

            // Recharger le portefeuille
            $wallet->increment('balance', $amount);

            // Enregistrer la transaction
            $transaction = Transaction::create([
                'sender_id' => null, // Topup n'a pas d'expéditeur
                'receiver_id' => $user->id,
                'amount' => $amount,
                'type' => 'topup',
                'status' => 'success',
                'description' => 'Rechargement de portefeuille'
            ]);

            return response()->json([
                'user_id' => $wallet->user_id,
                'balance' => (float) $wallet->balance,
                'topup_amount' => (float) $amount,
                'created_at' => $transaction->created_at->toISOString(),
            ], 201);
        });
    }

    /**
     * Obtenir l'historique des transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'sender_id', 'receiver_id', 'amount', 'type', 'status', 'created_at']);

        return response()->json($transactions);
    }
}
