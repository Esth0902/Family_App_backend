<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $userId = Auth::id();

        $recipes = Recipe::whereHas('household.users', function($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->orderBy('title', 'asc')
            ->get();

        return response()->json($recipes);
    }

    public function show($id)
    {
        $userId = Auth::id();
        $recipe = Recipe::with('ingredients')
            ->whereHas('household.users', function($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->where('id', $id)
            ->first();

        if (!$recipe) {
            return response()->json(['message' => 'Non autorisé ou recette introuvable'], 403);
        }

        return response()->json($recipe);
    }

    public function suggestIdeas(Request $request)
    {
        $request->validate([
            'preferences' => 'nullable|string',
            'count' => 'nullable|integer|max:10'
        ]);

        $ideas = $this->aiService->suggestMealIdeas(
            $request->count ?? 5,
            $request->preferences ?? ''
        );

        return response()->json($ideas);
    }

    public function storeFromAi(Request $request)
    {
        $validated = $request->validate([
            'household_id' => 'required|exists:households,id',
            'title' => 'required|string',
        ]);

        $details = $this->aiService->getFullRecipeDetails($validated['title']);

        if (!$details || !isset($details['ingredients'])) {
            return response()->json(['message' => "Erreur lors de la génération de la recette"]);
        }

        return DB::transaction(function () use ($details, $validated) {
            $recipe = Recipe::create([
                'household_id' => $validated['household_id'],
                'title' => $details['title'] ?? $validated['title'],
                'description' => $details['description'] ?? null,
                'instructions' => $details['instructions'] ?? null,
                'is_ai_generated' => true,
            ]);

            foreach ($details['ingredients'] as $item) {
                $ingredient = Ingredient::firstOrCreate([
                    'name' => strtolower($item['name']),
                    'unit' => $item['unit'] ?? 'unité',
                ]);

                $recipe->ingredients()->attach($ingredient->id,
                ['quantity' => $item['quantity'] ?? 1]);
            }

            return response()->json($recipe->load('ingredients'));
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'household_id' => 'required|exists:households,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.unit' => 'required|string',
            'ingredients.*.quantity' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($validated) {
            $recipe = Recipe::create([
                'household_id' => $validated['household_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'instructions' => $validated['instructions'],
                'is_ai_generated' => false,
            ]);

            foreach ($validated['ingredients'] as $item) {
                $ingredient = Ingredient::firstOrCreate(
                    ['name' => strtolower($item['name'])],
                    ['unit' => $item['unit']]
                );

                $recipe->ingredients()->attach($ingredient->id, [
                    'quantity' => $item['quantity']
                ]);
            }

            return response()->json($recipe->load('ingredients'));
        });
    }
}
