<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ingredient extends Model
{
    protected $fillable = ['name', 'unit'];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'ingredient_recipe')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
