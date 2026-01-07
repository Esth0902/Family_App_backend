<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function index(Request $request)
    {
        $list = ShoppingList::where('household_id', $request->get('household_id'))
            ->latest()
            ->with('items')
            ->first();

        return response()->json($list);
    }

    public function updateItem(Request $request, ShoppingListItem $item)
    {
        $item->update($request->only(['is_bought', 'quantity', 'name']));
        return response()->json($item);
    }

    public function addItem(Request $request, ShoppingList $list)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string',
        ]);

        $item = $list->items()->create(array_merge($validated, [
            'is_manual_addition' => true
        ]));

        return response()->json($item);
    }

    public function removeItem(ShoppingListItem $item)
    {
        $item->delete();
        return response()->json(['message' => 'Élement supprimé']);
    }


}
