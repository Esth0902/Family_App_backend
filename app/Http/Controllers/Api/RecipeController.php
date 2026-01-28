<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecipeController extends Controller
{
    use AuthorizesRequests;
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
        $recipe = Recipe::with('ingredients')->find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recette introuvable'], 404);
        }

        $this->authorize('view', $recipe);

        return response()->json($recipe);
    }

    public function suggestIdeas(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'nullable|string|max:500',
            'count' => 'nullable|integer|max:5',
            'intent' => 'nullable|string|in:ideas,specific'
        ]);

        $text = $validated['preferences'] ?? '';
        $intent = $validated['intent'] ?? 'ideas';
        $count = $validated['count'] ?? 3;

        if ($intent === 'specific') {
            $recipe = $this->aiService->getFullRecipeDetails($text);

            if (empty($recipe)) {
                return response()->json(['message' => 'Impossible de générer la recette'], 422);
            }

            return response()->json([
                'type' => 'single',
                'data' => $recipe
            ]);
        }

        $ideas = $this->aiService->suggestMealIdeas($count, $text);

        return response()->json([
            'type' => 'list',
            'data' => $ideas
        ]);
    }

    public function previewAiRecipe(Request $request)
    {
        $request->validate(['title' => 'required|string']);

        $details = $this->aiService->getFullRecipeDetails($request->title);

        return response()->json($details);
    }
    public function finalizeAiStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'ingredients' => 'required|array',
            'household_id' => 'required|exists:households,id',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.unit' => 'required|string',
            'ingredients.*.quantity' => 'required|numeric'
        ]);

        $this->authorize('create', [Recipe::class, (int)$validated['household_id']]);

        return DB::transaction(function () use ($validated) {

            $recipe = Recipe::create([
                'household_id' => $validated['household_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'instructions' => $validated['instructions'],
                'is_ai_generated' => true,
            ]);

            foreach ($validated['ingredients'] as $item) {
                $ingredient = Ingredient::firstOrCreate([
                    'name' => strtolower($item['name']),
                ]);

                $recipe->ingredients()->attach($ingredient->id, [
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                ]);
            }

            return response()->json($recipe->load('ingredients'), 201);
        });
    }

//    public function storeFromAi(Request $request)
//    {
//        $validated = $request->validate([
//            'household_id' => 'required|exists:households,id',
//            'title' => 'required|string',
//        ]);
//
//        $details = $this->aiService->getFullRecipeDetails($validated['title']);
//
//        if (!$details || !isset($details['ingredients'])) {
//            return response()->json(['message' => "Erreur lors de la génération de la recette"]);
//        }
//
//        return DB::transaction(function () use ($details, $validated) {
//            $recipe = Recipe::create([
//                'household_id' => $validated['household_id'],
//                'title' => $details['title'] ?? $validated['title'],
//                'description' => $details['description'] ?? null,
//                'instructions' => $details['instructions'] ?? null,
//                'is_ai_generated' => true,
//            ]);
//
//            foreach ($details['ingredients'] as $item) {
//                $ingredient = Ingredient::firstOrCreate([
//                    'name' => strtolower($item['name']),
//                    'unit' => $item['unit'] ?? 'unité',
//                ]);
//
//                $recipe->ingredients()->attach($ingredient->id,
//                ['quantity' => $item['quantity'] ?? 1]);
//            }
//
//            return response()->json($recipe->load('ingredients'));
//        });
//    }

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

        $this->authorize('create', [Recipe::class, (int)$validated['household_id']]);

        return DB::transaction(function () use ($validated) {
            $recipe = Recipe::create([
                'household_id' => $validated['household_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'instructions' => $validated['instructions'],
                'is_ai_generated' => false,
            ]);

            foreach ($validated['ingredients'] as $item) {
                $ingredient = Ingredient::firstOrCreate([
                    'name' => strtolower($item['name']),
                ]);

                $recipe->ingredients()->attach($ingredient->id, [
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                ]);
            }

            return response()->json($recipe->load('ingredients'));
        });
    }

    public function update(Request $request, $id)
    {
        $recipe = Recipe::with('ingredients')->find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recette introuvable'], 404);
        }

        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.unit' => 'required|string',
            'ingredients.*.quantity' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($recipe, $validated) {

            $recipe->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
            ]);

            $syncData = [];

            foreach ($validated['ingredients'] as $item) {
                $ingredient = Ingredient::firstOrCreate([
                    'name' => strtolower($item['name']),
                ]);

                $syncData[$ingredient->id] = [
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                ];
            }

            $recipe->ingredients()->sync($syncData);

            return response()->json($recipe->load('ingredients'));
        });
    }
    public function destroy($id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recette introuvable'], 404);
        }

        $this->authorize('delete', $recipe);

        return DB::transaction(function () use ($recipe) {
            $recipe->ingredients()->detach();
            $recipe->delete();

            return response()->json(['message' => 'Recette supprimée']);
        });
    }
}
