<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request){
        if(!Auth::attempt($request->validated())){
            return $this->sendError('Incorrect email or password', null, 401);
        }
        $data['user'] = Auth::user();
        $data['token'] = Auth::user()->createToken('auth_token')->plainTextToken;
        return $this->sendResponse($data, 'Login successful');
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Logout successful', 200);   
    }
}
