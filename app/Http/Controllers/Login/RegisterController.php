<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class RegisterController extends BaseController
{
    public function register(RegisterRequest $request)
    {
        return $this->executeFunction(function () use ($request) {
            // Create the new user
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);

            // Generate a Sanctum token for the new user
            $token = $user->createToken('auth_token')->plainTextToken;

            $success['token'] = $token;
            $success['user'] = $user;

            return $success;
        });
    }
}
