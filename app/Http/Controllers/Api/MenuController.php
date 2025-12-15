<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function generate(Request $request, AiService $aiService): JsonResponse {
        $validated = $request->validate([
            'nb_repas' => 'required|integer|min:1|max:14',
            'preferences' => 'nullable|string|max:500',
        ]);

        $menu = $aiService->generateWeeklyMenu(
            $validated['nb_repas'],
            $validated['preferences'] ?? ''
        );
        if (empty($menu)) {
            return response()->json([
                'message' => 'Impossible de générer le menu pour le moment. Veuillez réessayer.',
                'error' => 'AI_SERVICE_UNAVAILABLE'
            ], 503);
        }
        return response()->json([
            'success' => true,
            'date' => $menu,
            'meta' => [
                'count' => count($menu),
                'generated_at' => now()->toIso8601String()
            ]
        ]);
    }
}
