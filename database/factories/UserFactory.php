<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'password' => '$2y$10$XIMtc5udo6rlzgWYIY2WIOqGZ9NhmeJFsVJuApXn6Y7xd5t2./ydy' //root
        ];
    }
}
