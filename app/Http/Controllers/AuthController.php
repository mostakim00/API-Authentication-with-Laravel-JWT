<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{
    public function sendResponse($data, $message, $status = 200)
    {
        $response = [
            'data' => $data,
            'message' => $mesage
        ];
        return response()->json($response, $status);
    }
    public function sendError($errorDataBag, $message, $status = 500)
    {
        $response = [];
        $response['message'] = $message;
        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }
        return $response()->json($response, $status);
    }
    public function register(Request $request)
    {
        $input = $request->only('name', 'email', 'password', 'c_password');

        $validator = Validator::make($input, [
           'name' => 'require',
            'email' => 'require|email|unique:users',
            'password' => 'require|min:8',
            'c_password' => 'require|same:password',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }
        $input['password'] = bcrypt($input['password']); // use bcrypt to hash the passwords
        $user = User::create($input); // eloquent creation of data

        $success['user'] = $user;

        return $this->sendResponse($success, 'user registered successfully', 201);
    }
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');

        $validator = Validator::make($input, [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }
        try {
            if (! $token = JWTAuth::attempt($input)) {
            return $this->sendError([], "Invalid Login Credentials", 400);
            }
        } catch (JWTException $e){
            return $this->sendError([], $e->getMessage(), 500);
        }
        $success = [
            'token' => $token,
        ];
        return $this->sendResponse($success, 'successful login', 200);
        }
    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return $this->sendError([], "user not found", 403);
            }
        } catch (JWTException $e) {
            return $this->sendError([], $e->getMessage(), 500);
        }

        return $this->sendResponse($user, "user data retrieved", 200);
    }
}
