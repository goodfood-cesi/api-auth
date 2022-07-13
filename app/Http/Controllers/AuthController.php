<?php

namespace App\Http\Controllers;

use App\Mails\RegistrationSuccessful;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use ReCaptcha\ReCaptcha;

class AuthController extends Controller {

    public function login(Request $request): JsonResponse {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
            'recaptcha' => 'required|string'
        ]);

        if (getenv('APP_ENV') === 'production') {
            $recaptcha = new Recaptcha(getenv('RECAPTCHA_SECRET_KEY'));
            $resp = $recaptcha->verify($request->input('recaptcha'), $request->ip());

            if (!$resp->isSuccess()) {
                return $this->error('Captcha not OK', [], 401);
            }
        }

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return $this->error('E-mail or password incorrect', [], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'firstname' => 'required|string|min:2',
            'lastname' => 'required|string|min:2',
            'recaptcha' => 'required|string'
        ]);

        if (getenv('APP_ENV') === 'production') {
            $recaptcha = new Recaptcha(getenv('RECAPTCHA_SECRET_KEY'));
            $resp = $recaptcha->verify($request->input('recaptcha'), $request->ip());

            if (!$resp->isSuccess()) {
                return $this->error('Captcha not OK', [], 401);
            }
        }

        $credentials = $request->only(['email', 'password', 'firstname', 'lastname']);

        $credentials['password'] = Hash::make($credentials['password']);
        $user = User::create($credentials);
        try {
            Mail::to($user->email)->send(new RegistrationSuccessful($user));
        } catch (\Exception $e) {
            die(var_dump($e->getMessage()));
        }

        return $this->ressourceCreated($user, 'User registered.');
    }

    public function refresh() {
        return $this->respondWithToken(auth()->refresh(true));
    }

    public function user(): JsonResponse {
        $user = auth()->user();
        return $this->success($user, 'User loaded');
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
            'password' => 'required|string|min:6|confirmed'
        ]);

        $credentials = $request->only(['password', 'password_confirmation']);
        $credentials['password'] = Hash::make($credentials['password']);

        if ($request->has('token')) {
            $user = User::where('reset_password', $request->input('token'))->firstOrFail();
            $credentials['reset_password'] = null;
        } else {
            $user = auth()->user();
        }
        if ($user) {
            $user->update($credentials);
            if (auth()->user()) {
                auth()->logout();
            }
            return $this->successWithoutData('Successfully edited password');
        }
        return $this->error();
    }

    public function forgot_password(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
        ]);
        $credentials = $request->only(['email']);
        $user = User::firstWhere('email', $credentials['email']);
        if ($user) {
            $user->update(['reset_password' => bin2hex(random_bytes(32))]);
        }
        return $this->successWithoutData('Request Sent !');
    }

    public function reset_password(Request $request) {
        $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);
        $credentials = $request->only(['token', 'password']);
        $credentials['password'] = Hash::make($credentials['password']);
        $user = User::firstWhere('reset_password', $credentials['token']);
        if ($user) {
            $user->update($credentials);
            return $this->successWithoutData('Successfully edited password');
        }
        return $this->error();
    }

    public function delete(Request $request): JsonResponse {
        User::where('id', auth()->user()->id)->delete();
        auth()->logout();
        return $this->ressourceDeleted();
    }

    public function logout(): JsonResponse {
        auth()->logout();
        return $this->successWithoutData('Successfully logged out');
    }
}
