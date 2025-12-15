<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseholdInvitation extends Model
{
    use HasFactory;
    protected $fillable = ['household_id', 'inviter_id', 'token', 'email', 'expires_at'];
}
