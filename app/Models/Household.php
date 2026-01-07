<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'settings', 'poll_day', 'poll_time', 'poll_duration'];
    protected $casts = [
        'settings' => 'array',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'nickname')
            ->withTimestamps();
    }
    public function invitations()
    {
        return $this->hasMany(HouseholdInvitation::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function shoppingLists()
    {
        return $this->hasMany(ShoppingList::class);
    }
}
