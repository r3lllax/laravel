<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\UserAddRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(){
        return UserResource::collection(User::all());
    }


    public function login(UserLoginRequest $request)
    {

        $user = User::where([
            'login'=>$request->login,
            'password'=>$request->password,
        ])->first();
        if(!$user){
            throw new ApiException(401,"Autentification failed");
        }

        return response()->json([
            'data'=> [
                'user_token'=>$user->generateToken()
            ],
        ]);
    }

    public function logout()
    {
        Auth::user()->logout();
        return [
            'data'=>[
                'message' => 'logout'
            ]
        ];
    }

    public function store(UserAddRequest $userRequest)
    {
        return $userRequest;
    }

}
