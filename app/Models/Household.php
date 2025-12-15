<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'settings'];
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
}
