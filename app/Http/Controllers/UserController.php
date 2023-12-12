<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->only('email', 'name', 'password'), [
            'name' => 'unique:users|required',
            'email'    => 'unique:users|required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newUser = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);

        $generatedToken = $newUser->createToken('auth_token');

        return response()->json(['token' => $generatedToken->plainTextToken]);
    }

    public function login(Request $request)
    {
        $authSuccessful = Auth::attempt($request->only('email', 'password'));
        if(!$authSuccessful) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $user->tokens()->delete();
        $generatedToken = $user->createToken('auth_token');
        return response()->json(['token' => $generatedToken->plainTextToken]);
    }
}
