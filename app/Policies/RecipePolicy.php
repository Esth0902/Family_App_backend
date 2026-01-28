<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    private function pivotRole(User $user, int $householdID): ?string
    {
        $household = $user->households()->where('households.id', $householdID)->first();
        return $household ? $household->pivot->role : null;
    }

    private function isHouseholdAdmin(User $user, int $householdID): bool
    {
        return $this->pivotRole($user, $householdID) === 'admin';
    }

    private function isHouseholdMember(User $user, int $householdID): bool
    {
        return !is_null($this->pivotRole($user, $householdID));
    }

    public function viewAny(User $user, int $householdID): bool
    {
        return $this->isHouseholdMember($user, $householdID);
    }

    public function view(User $user, Recipe $recipe): bool
    {
        return $this->isHouseholdMember($user, $recipe->household_id);
    }

    public function create(User $user, int $householdID): bool
    {
        return $this->isHouseholdAdmin($user, $householdID);
    }

    public function update(User $user, Recipe $recipe): bool
    {
        return $this->isHouseholdAdmin($user, $recipe->household_id);
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return $this->isHouseholdAdmin($user, $recipe->household_id);
    }
}
