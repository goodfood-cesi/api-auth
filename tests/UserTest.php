<?php

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UserTest extends TestCase {
    use DatabaseMigrations;

    public function test_can_create_user() {
        $user = User::factory()->make();

        $this->post(route('users.register'), [
            'email' => $user->email,
            'password' => $user->password,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
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

    public function test_cannot_create_user() {
        User::factory()->create([
            'email' => 'root@example.com'
        ]);
        $user = User::factory()->make([
            'email' => 'root@example.com'
        ]);

        $this->post(route('users.register'), [
            'email' => $user->email,
            'password' => $user->password,
            'last_name' => $user->lastname,
            'first_name' => $user->firstname,
        ]);

        $this->seeStatusCode(422);
    }

//    public function test_can_login() {
//        User::factory()->create([
//            'email' => 'root@example.com'
//        ]);
//
//        $this->post(route('users.login'), [
//            'email' => 'root@example.com',
//            'password' => 'root',
//        ]);
//
//        $this->seeStatusCode(200);
//
//        $this->seeJsonStructure([
//            'token',
//            'token_type',
//            'expires_in'
//        ]);
//    }

    public function test_cannot_login() {
        User::factory()->create([
            'email' => 'root@example.com'
        ]);

        $this->post(route('users.login'), [
            'email' => 'root@example.com',
            'password' => 'rooot',
        ]);

        $this->seeStatusCode(401);

        $this->seeJsonStructure([
            'message',
            'code'
        ]);
    }
}
