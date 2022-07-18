<?php

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UserTest extends TestCase {
    use DatabaseMigrations;

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\AuthController
     * @return void
     */
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\AuthController
     * @covers App\Exceptions\Handler
     * @return void
     */
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_update_user(): void {
        $user = User::factory()->create();

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'root',
            'recaptcha' => 'test'
        ]);

        $user2 = User::factory()->make();

        $this->post(route('users.edit'), [
            'lastname' => $user2->lastname,
            'firstname' => $user2->firstname,
        ], ['Authorization' => "Bearer ". json_decode($this->response->getContent())->data->token]);

        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => null,
            'meta' => [
                'success' => true,
                'message' => 'Successfully edited user'
            ]
        ]);

        $this->seeInDatabase('users', [
            'id' => $user->id,
            'email' => $user->email,
            'firstname' => $user2->firstname,
            'lastname' => $user2->lastname
        ]);
    }

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\AuthController
     * @return void
     */
    public function test_cannot_update_user(): void {
        $user = User::factory()->make();

        $this->post(route('users.edit'), [
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
        ]);

        $this->seeStatusCode(401);
        $this->seeJson([
            'data' => 'Token not provided',
            'meta' => [
                'success' => false,
                'message' => 'Unauthorized'
            ]
        ]);
    }

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_delete_user(): void {
        $user = User::factory()->create();

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'root',
            'recaptcha' => 'test'
        ]);

        $this->delete(route('users.delete'), [], ['Authorization' => "Bearer ". json_decode($this->response->getContent())->data->token]);

        $this->seeStatusCode(204);

        $this->notSeeInDatabase('users', [
            'id' => $user->id,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'deleted_at' => null
        ]);
    }

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\AuthController
     * @return void
     */
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_refresh_token(): void {
        $this->post(route('users.refresh'), [], [
            'Authorization' => 'Bearer ' . $this->token()
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_logout(): void {
        $this->post(route('users.logout'), [], [
            'Authorization' => 'Bearer ' . $this->token()
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_get_me(): void {
        $this->get(route('users.me'), ['Authorization' => 'Bearer '. $this->token()]);
        $this->seeStatusCode(200);
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

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\AuthController
     * @return void
     */
    public function test_can_forgot_password(): void {
        $user = User::factory()->create();
        $this->post(route('users.forgot'), [
            'email' => $user->email,
            'recaptcha' => 'test'
        ]);
        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => null,
            'meta' => [
                'success'=> true,
                'message' => 'Request Sent !'
            ]
        ]);
        $this->notseeInDatabase('users', [
            'id' => $user->id,
            'reset_password' => null
        ]);
    }

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\AuthController
     * @return void
     */
    public function test_can_verify_token(): void {
        $user = User::factory()->create([
            'reset_password' => 'test'
        ]);

        $this->get(route('users.reset', ['reset_password' => $user->reset_password]));
        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
            ],
            'meta' => [
                'success'=> true,
                'message' => 'Token OK'
            ]
        ]);
    }

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_reset_password(): void {
        $user = User::factory()->create([
            'reset_password' => 'test'
        ]);

        $this->post(route('users.reset'), [
            'reset_password' => $user->reset_password,
            'password' => 'rootroot',
            'password_confirmation' => 'rootroot',
        ]);
        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => null,
            'meta' => [
                'success'=> true,
                'message' => 'Successfully edited password'
            ]
        ]);

        $this->seeInDatabase('users', [
            'id' => $user->id,
            'reset_password' => null
        ]);

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'rootroot',
            'recaptcha' => 'test'
        ]);
        $this->seeStatusCode(200);
    }

    /**
     * @covers App\Http\Middleware\Authenticate
     * @covers App\Traits\ApiResponser
     * @covers App\Http\Controllers\Controller
     * @covers App\Http\Controllers\AuthController
     * @covers App\Models\User
     * @return void
     */
    public function test_can_update_password(): void {
        $user = User::factory()->create();
        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'root',
            'recaptcha' => 'test'
        ]);

        $this->post(route('users.password'), [
            'password' => 'rootroot',
            'password_confirmation' => 'rootroot',
        ], ['Authorization' => "Bearer ". json_decode($this->response->getContent())->data->token]);
        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => null,
            'meta' => [
                'success'=> true,
                'message' => 'Successfully edited password'
            ]
        ]);

        $this->post(route('users.login'), [
            'email' => $user->email,
            'password' => 'rootroot',
            'recaptcha' => 'test'
        ]);
        $this->seeStatusCode(200);
    }
}
