<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(StoreUserRequest $request, UserService $userService)
    {
        $newUser = $userService->createUser($request);
        $generatedToken = $userService->getNewTokenForUser($newUser);

        return response()->json(['token' => $generatedToken->plainTextToken], 201);
    }

    public function login(Request $request, UserService $userService)
    {
        $authSuccessful = Auth::attempt($request->only('email', 'password'));
        if (! $authSuccessful) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $generatedToken = $userService->getNewTokenForUser($user);

        return response()->json(['token' => $generatedToken->plainTextToken]);
    }
}
