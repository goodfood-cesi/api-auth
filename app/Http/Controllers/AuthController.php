<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    public function login(Request $request): JsonResponse {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'E-mail or password incorrect', 'code' => 401002], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password', 'firstname', 'lastname']);

        $credentials['password'] = Hash::make($credentials['password']);
        $user = User::create($credentials);

        return $this->ressourceCreated($user, 'User registered.');
    }

    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }

    public function user(): JsonResponse {
        $user = auth()->user();
        return $this->success($user);
    }

    public function edit(Request $request): JsonResponse {
        $this->validate($request, [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
        ]);

        $credentials = $request->only(['firstname', 'lastname']);
        auth()->user()->update($credentials);
        return $this->successWithoutData('Successfully edited user');
    }

    public function password(Request $request): JsonResponse {
        $this->validate($request, [
            'password' => 'required|string'
        ]);

        $credentials = $request->only(['password']);
        $credentials['password'] = Hash::make($credentials['password']);
        auth()->user()->update($credentials);
        auth()->logout();
        return $this->successWithoutData('Successfully edited password, you have been logged out');
    }

    public function logout(): JsonResponse {
        auth()->logout();
        return $this->successWithoutData('Successfully logged out');
    }
}
