<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private const TEST_USER = [
        'name' => 'John',
        'email' => 'john@hi.dev',
        'password' => 'password', // default password,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the main user
        User::factory()->create([
            'name' => self::TEST_USER['name'],
            'email' => self::TEST_USER['email'],
            'password' => Hash::make(self::TEST_USER['password']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);


        User::factory(5)->create();

        $this->call([
            ProductSeeder::class,    // Create products
        ]);
    }
}
