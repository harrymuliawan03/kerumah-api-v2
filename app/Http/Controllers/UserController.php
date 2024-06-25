<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if (User::where('email', $data['email'])->count() == 1) {
                return ApiResponse::error('Email already registered.', 400);
            }

            $user = new User($data);
            $user->password = Hash::make($data['password']);
            $user->save();

            return response()->json(ApiResponse::success('Register Successfully', new UserResource($user)), 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            // dd($data['email']);

            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return ApiResponse::error('Username or password wrong', 400);
            }

            $user->token = Str::uuid()->toString();
            $user->save();

            return response()->json(ApiResponse::success('Login Successfully', new UserResource($user)), 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    public function getUser(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json(ApiResponse::success('Get User Successfully', new UserResource($user)), 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    public function getUserByToken(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        try {
            if ($token) {
                $user = User::where('token', $token)->first();
            }

            if (!$token || !$user) {
                return ApiResponse::error('Token or User not found', 400);
            }
            return response()->json(ApiResponse::success('Get User Successfully', new UserResource($user)), 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }


    public function updateUser(UserUpdateRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = Auth::user();

            if (isset($data['name'])) {
                $user->name = $data['name'];
            }

            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            return response()->json(ApiResponse::success('Update User Successfully', new UserResource($user)), 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }
}
