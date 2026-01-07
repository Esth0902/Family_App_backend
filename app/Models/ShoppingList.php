<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingList extends Model
{
    protected $fillable = ['household_id', 'meal_poll_id'];

    public function items()
    {
        return $this->hasMany(ShoppingListItem::class);
    }

}
