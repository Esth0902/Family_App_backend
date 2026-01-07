<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Models\MealPoll;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MealPollController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'household_id' => 'required|exists:households,id',
            'recipe_ids' => 'required|array|min:1',
            'recipe_ids.*' => 'exists:recipes,id',
            'duration_hours' => 'nullable|integer|min:1'
        ]);

        $duration = $validated['duration_hours'] ?? 24;

        $poll = MealPoll::create([
            'household_id' => $validated['household_id'],
            'starts_at' => now(),
            'ends_at' => now()->addHours($duration),
            'status' => 'open'
        ]);

        $poll->options()->attach($validated['recipe_ids']);

        return response()->json($poll->load('options'));
    }

    public function vote(Request $request, MealPoll $poll)
    {
        $this->authorize('vote', $poll);

        $validated = $request->validate([
            'recipe_id' => 'required|integer|exists:recipes,id',
        ]);

        if ($poll->status !== 'open' || now()->gt($poll->ends_at)) {
            return response()->json(['message' => 'Sondage clôturé'], 403);
        }

        if (!$poll->options->contains($validated['recipe_id'])) {
            return response()->json(['message' => 'Cette recette ne fait pas partie du sondage'], 422);
        }


        $userId = $request->user()->id;
        $recipeId = $validated['recipe_id'];

        $existingVote = $poll->votes()
            ->where('user_id', $userId)
            ->where('recipe_id', $recipeId)
            ->first();

        if ($existingVote) {
            $existingVote->delete();
            $message = 'Vote retiré';
            $voted = false;
        } else {
            $poll->votes()->create([
                'user_id' => $userId,
                'recipe_id' => $recipeId,
            ]);
            $message = 'Vote ajouté';
            $voted = true;
        }

        return response()->json([
            'message' => $message,
            'status' => $voted,
        ]);
    }

    public function validateResults(MealPoll $poll)
    {
        return DB::transaction(function () use ($poll) {
            $poll->update(['status' => 'validated']);
            $winningRecipeIds = $poll->votes()
                ->select('recipe_id', DB::raw('count(*) as total'))
                ->groupBy('recipe_id')
                ->orderByDesc('total')
                ->pluck('recipe_id');

            $shoppingList = ShoppingList::create([
                'household_id' => $poll->household_id,
                'meal_poll_id' => $poll->id
            ]);

            $ingredients = Ingredient::query()
                ->join('ingredient_recipe', 'ingredients.id', '=', 'ingredient_recipe.ingredient_id')
                ->whereIn('ingredient_recipe.recipe_id', $winningRecipeIds)
                ->select(
                    'ingredients.name',
                    'ingredients.unit',
                    DB::raw('SUM(ingredient_recipe.quantity) as total_quantity')
                )
                ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit')
                ->get();

            foreach ($ingredients as $ingredient) {
                ShoppingListItem::create([
                    'shopping_list_id' => $shoppingList->id,
                    'name' => $ingredient->name,
                    'quantity' => $ingredient->total_quantity,
                    'unit' => $ingredient->unit,
                    'is_bought' => false,
                    'is_manual_addition' => false
                ]);
            }

            return response()->json([
                'message' => 'Sondage validé et liste de courses générée',
                'shopping_list' => $shoppingList->load('items'),
            ]);
        });
    }

}
