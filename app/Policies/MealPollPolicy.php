<?php

namespace App\Policies;

use App\Models\MealPoll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MealPollPolicy
{
    /**
     * Determine the role of the user in the household
     */
    private function getRoleInHousehold(User $user, int $householdID): ?string
    {
        $household = $user->households()->where('households.id', $householdID)->first();
        return $household ? $household->pivot->role : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MealPoll $mealPoll): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $householdID): bool
    {
        $role = $this->getRoleInHousehold($user, $householdID);
        return $role === User::ROLE_PARENT;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MealPoll $mealPoll): bool
    {
        return $this->getRoleInHousehold($user, $mealPoll->household_id) === User::ROLE_PARENT;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MealPoll $mealPoll): bool
    {
        return $this->getRoleInHousehold($user, $mealPoll->household_id) === User::ROLE_PARENT;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MealPoll $mealPoll): bool
    {
        return $this->getRoleInHousehold($user, $mealPoll->household_id) === User::ROLE_PARENT;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MealPoll $mealPoll): bool
    {
        return $this->getRoleInHousehold($user, $mealPoll->household_id) === User::ROLE_PARENT;
    }

    /**
     * Determine whether the user can vote for the poll.
     */
    public function vote(User $user, MealPoll $mealPoll): bool
    {
        return !is_null($this->getRoleInHousehold($user, $mealPoll->household_id));
    }
}
