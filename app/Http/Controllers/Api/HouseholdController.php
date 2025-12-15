<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->households()->exists()) {
            return response()->json([
                'message' => 'Vous appartenez déjà à un foyer. Impossible d\'en créer un autre.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'modules' => 'array',
            'children_profiles' => 'array',
            'children_profiles.*' => 'string'
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($validated, $user) {

            $household = Household::create([
                'name' => $validated['name'],
                'settings' => $validated['modules'] ?? [],
            ]);

            $household->users()->attach($user->id, [
                'role' => 'admin',
                'nickname' => 'Parent'
            ]);

            if (!empty($validated['children_profiles'])) {
                foreach ($validated['children_profiles'] as $childName) {

                    $dummyEmail = 'child_' . Str::random(10) . '@placeholder.app';

                    $childUser = User::create([
                        'name' => $childName,
                        'email' => $dummyEmail,
                        'password' => Hash::make(Str::random(32)),
                        'role' => 'child'
                    ]);

                    $household->users()->attach($childUser->id, [
                        'role' => 'child',
                        'nickname' => $childName
                    ]);
                }
            }

            return response()->json([
                'message' => 'Foyer créé avec succès !',
                'household' => $household,
                'user' => $user->load('households')
            ], 201);
        });
    }
    public function createInvitation(Request $request, $householdId)
    {
        $user = $request->user();

        $household = Household::findOrFail($householdId);

        $token = Str::random(32);

        $invitation = HouseholdInvitation::create([
            'household_id' => $household->id,
            'inviter_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        $link = "myapp://join-household?token=" . $token;

        return response()->json([
            'link' => $link,
            'message' => 'Lien généré',
            'expires_at' => $invitation->expires_at
        ]);
    }
    public function join(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string'
        ]);

        $invitation = HouseholdInvitation::where('token', $validated['token'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Lien invalide ou expiré'], 404);
        }

        $user = $request->user();

        if ($user->households()->where('household_id', $invitation->household_id)->exists()) {
            return response()->json(['message' => 'Vous êtes déjà membre de ce foyer.']);
        }

        $user->households()->attach($invitation->household_id, [
            'role' => 'member'
        ]);

        $invitation->delete();

        return response()->json([
            'message' => 'Foyer rejoint avec succès !',
            'household' => Household::find($invitation->household_id)
        ]);
    }
}
