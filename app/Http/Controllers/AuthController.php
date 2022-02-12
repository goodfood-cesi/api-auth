<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mailjet\Client;
use Mailjet\Resources;
use ReCaptcha\ReCaptcha;

class AuthController extends Controller {

    public function login(Request $request): JsonResponse {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
            'recaptcha' => 'required|string'
        ]);

        $recaptcha = new ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY']);
        $resp = $recaptcha->verify($request->input('recaptcha'), $_SERVER["REMOTE_ADDR"]);
        if (!$resp->isSuccess()) {
            return $this->error('Captcha not OK', [], 401);
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

        $recaptcha = new ReCaptcha($_ENV['RECAPTCHA_SECRET_KEY']);
        $resp = $recaptcha->verify($request->input('recaptcha'), $_SERVER["REMOTE_ADDR"]);
        if (!$resp->isSuccess()) {
            return $this->error('Captcha not OK', [], 401);
        }

        $credentials = $request->only(['email', 'password', 'firstname', 'lastname']);

        $credentials['password'] = Hash::make($credentials['password']);
        $user = User::create($credentials);

        $mj = new Client($_ENV['MJ_APIKEY_PUBLIC'], $_ENV['MJ_APIKEY_PRIVATE'], true, ['version' => 'v3.1']);
        $mj->post(Resources::$Email, [
            'body' => [
                'Messages' => [
                    [
                        'To' => [
                            [
                                'Email' => $credentials['email'],
                                'Name' => $credentials['firstname'] . ' ' . $credentials['lastname']
                            ]
                        ],
                        'TemplateID' => 3620716,
                        'TemplateLanguage' => true,
                        'Subject' => 'Bienvenue sur GoodFood '.$credentials['firstname'].' !',
                        'Variables' => ['firstname' => $credentials['firstname']],
                    ]
                ]
            ]
        ]);

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
