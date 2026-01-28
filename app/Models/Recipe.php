<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['household_id', 'title', 'description', 'instructions', 'is_ai_generated'];
    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_recipe')
            ->withPivot('quantity', 'unit')
            ->withTimestamps();
    }
}
