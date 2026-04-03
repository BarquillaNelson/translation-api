<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

class LoginController extends BaseController
{
    public function login(LoginRequest $request)
    {
        return $this->executeFunction(function () use ($request) {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
                $user = Auth::user(); 
                $login = User::find($user->id)->login($user->email);

                $tokenResult = $user->createToken('authToken'); 
                $success['token'] =  $tokenResult->plainTextToken; 

                $expirationDate = $tokenResult->accessToken->expires_at;
                $success['token_expires_at'] = Carbon::parse($expirationDate)->format('Y-m-d H:i:s');
                $success['user'] =  $login;
            } 

            return $success;
        });
    }

    public function logout()
    {
        return $this->executeFunction(function () {
            $accessToken = Auth::user()->currentAccessToken();
            $token = $accessToken->delete();

            return $token;
        });
    }
}
