<?php

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UserTest extends TestCase {
    use DatabaseMigrations;

    public function test_can_create_user(): void {
        $user = User::factory()->make();

        $this->post(route('users.register'), [
            'email' => $user->email,
            'password' => $user->password,
            'password_confirmation' => $user->password,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'recaptcha' => 'test'
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'email',
                'firstname',
                'lastname',
                'id'
            ],
            'meta' => [
                'success',
                'message'
            ]
        ]);
    }

    public function test_cannot_create_user(): void {
        User::factory()->create([
            'email' => 'root@example.com'
        ]);
        $user = User::factory()->make([
            'email' => 'root@example.com'
        ]);

        $this->post(route('users.register'), [
            'email' => $user->email,
            'password' => $user->password,
            'password_confirmation' => $user->password,
            'last_name' => $user->lastname,
            'first_name' => $user->firstname,
            'recaptcha' => 'test'
        ]);

        $this->seeStatusCode(422);
    }

    public function test_can_login(): void {
        $user = User::factory()->create();

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'root',
            'recaptcha' => 'test'
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'token',
                'token_type',
                'expires_in',
            ],
            'meta' => [
                'success',
                'message'
            ]
        ]);
    }

    public function test_cannot_login(): void {
        $user = User::factory()->create();

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'wrong',
            'recaptcha' => 'test'
        ]);

        $this->seeStatusCode(401);
        $this->seeJsonStructure([
            'data',
            'meta' => [
                'success',
                'message',
            ]
        ]);
    }

    public function test_can_refresh_token(): void {
        $user = User::factory()->create();

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'root',
            'recaptcha' => 'test'
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'token',
                'token_type',
                'expires_in',
            ],
            'meta' => [
                'success',
                'message'
            ]
        ]);

        $this->post(route('users.refresh'), [
            'token' => json_decode($this->response->getContent())->data->token
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'token',
                'token_type',
                'expires_in',
            ],
            'meta' => [
                'success',
                'message'
            ]
        ]);
    }

    public function test_can_logout(): void {
        $user = User::factory()->create();

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'root',
            'recaptcha' => 'test'
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'token',
                'token_type',
                'expires_in',
            ],
            'meta' => [
                'success',
                'message'
            ]
        ]);

        $this->post(route('users.logout'), [
            'token' => json_decode($this->response->getContent())->data->token
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data',
            'meta' => [
                'success',
                'message',
            ]
        ]);
    }

//    public function test_can_get_user(): void {
//        $user = User::factory()->create();
//
//        $this->get(route('users.show', $user->id));
//        $this->seeStatusCode(200);
//        $this->seeJsonStructure([
//            'data' => [
//                'email',
//                'firstname',
//                'lastname',
//                'id'
//            ],
//            'meta' => [
//                'success',
//                'message'
//            ]
//        ]);
//    }
//
//    public function test_cannot_get_user(): void {
//        $this->get(route('users.show', 'wrong'));
//        $this->seeStatusCode(404);
//    }
}
