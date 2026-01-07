<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model
{
    protected $fillable = [
        'shopping_list_id', 'name', 'quantity', 'unit', 'is_bought', 'is_manual_addition'
    ];

    protected $casts = [
        'is_bought' => 'boolean',
        'is_manual_addition' => 'boolean',
    ];
}
