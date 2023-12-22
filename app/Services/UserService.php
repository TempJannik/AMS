<?php

namespace App\Services;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(StoreUserRequest $userData): User
    {
        return User::create([
            'name' => $userData->post('name'),
            'email' => $userData->post('email'),
            'password' => Hash::make($userData->post('password')),
        ]);
    }

    public function getNewTokenForUser(User $user)
    {
        $user->tokens()->delete();
        $generatedToken = $user->createToken('auth_token');
        return $generatedToken;
    }
}