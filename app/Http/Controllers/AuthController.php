<?php

namespace App\Http\Controllers;

use App\Mails\PasswordUpdated;
use App\Mails\RegistrationSuccessful;
use App\Mails\ResetPassword;
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

        if (getenv('APP_ENV') === 'production') {
            Mail::to($user->email)->send(new RegistrationSuccessful($user));
        }

        return $this->ressourceCreated($user, 'User registered.');
    }

    public function refresh(): JsonResponse {
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

        auth()->user()->update($credentials);
        if (auth()->user()) {
            auth()->logout();
        }
        return $this->successWithoutData('Successfully edited password');
    }

    public function forgotPassword(Request $request): JsonResponse {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $credentials = $request->only(['email']);

        if (getenv('APP_ENV') === 'production') {
            $recaptcha = new Recaptcha(getenv('RECAPTCHA_SECRET_KEY'));
            $resp = $recaptcha->verify($request->input('recaptcha'), $request->ip());

            if (!$resp->isSuccess()) {
                return $this->error('Captcha not OK', [], 401);
            }
        }

        $user = User::firstWhere('email', $credentials['email']);
        if ($user) {
            $user->update(['reset_password' => bin2hex(random_bytes(32))]);
            if (getenv('APP_ENV') === 'production') {
                Mail::to($user->email)->send(new ResetPassword($user));
            }
        }
        return $this->successWithoutData('Request Sent !');
    }

    public function resetPassword(Request $request): JsonResponse {
        $this->validate($request, [
            'reset_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $credentials = $request->only(['password', 'password_confirmation']);
        $credentials['password'] = Hash::make($credentials['password']);
        $credentials['reset_password'] = null;

        $user = User::where('reset_password', $request->input('reset_password'))->firstOrFail();
        $user->update($credentials);

        if (getenv('APP_ENV') === 'production') {
            Mail::to($user->email)->send(new PasswordUpdated($user));
        }

        return $this->successWithoutData('Successfully edited password');
    }

    public function checkToken(Request $request): JsonResponse {
        $this->validate($request, [
            'reset_password' => 'required|string'
        ]);

        $user = User::where('reset_password', $request->input('reset_password'))->firstOrFail();
        return $this->success($user, 'Token OK');
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
