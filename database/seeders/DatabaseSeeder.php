<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            "email" => "root@example.com",
            "password" => '$2y$10$XIMtc5udo6rlzgWYIY2WIOqGZ9NhmeJFsVJuApXn6Y7xd5t2./ydy' //root
        ]);
    }
}
