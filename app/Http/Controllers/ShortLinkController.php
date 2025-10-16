<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShortenUrlRequest;
use App\Models\ShortLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ShortLinkController extends Controller
{
    /**
     * Créer un lien court
     */
    public function shorten(ShortenUrlRequest $request): JsonResponse
    {
        $code = $request->custom_code ?? ShortLink::generateUniqueCode();

        $shortLink = ShortLink::create([
            'user_id' => $request->user()->id,
            'original_url' => $request->original_url,
            'code' => $code,
            'clicks' => 0,
        ]);

        return response()->json([
            'id' => $shortLink->id,
            'user_id' => $shortLink->user_id,
            'original_url' => $shortLink->original_url,
            'code' => $shortLink->code,
            'clicks' => $shortLink->clicks,
            'created_at' => $shortLink->created_at->toISOString(),
        ], 201);
    }

    /**
     * Rediriger vers l'URL originale
     */
    public function redirect($code)
    {
        $shortLink = ShortLink::where('code', $code)->first();

        if (!$shortLink) {
            return response()->json([
                'error' => 'Lien court non trouvé'
            ], 404);
        }

        // Incrémenter le compteur de clics
        $shortLink->incrementClicks();

        return Redirect::away($shortLink->original_url);
    }

    /**
     * Lister tous les liens de l'utilisateur
     */
    public function index(Request $request): JsonResponse
    {
        $shortLinks = ShortLink::where('user_id', $request->user()->id)
            ->get(['id', 'original_url', 'code', 'clicks', 'created_at']);

        return response()->json($shortLinks);
    }

    /**
     * Supprimer un lien
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $shortLink = ShortLink::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$shortLink) {
            return response()->json([
                'error' => 'Lien non trouvé'
            ], 404);
        }

        $shortLink->delete();

        return response()->json([
            'message' => 'Link deleted successfully'
        ]);
    }
}
