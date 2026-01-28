<?php

namespace App\Providers;

use App\Models\MealPoll;
use App\Models\Recipe;
use App\Policies\MealPollPolicy;
use App\Policies\RecipePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Recipe::class => RecipePolicy::class,
        MealPoll::class => MealPollPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
