<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'full_name' => 'Test User',
            'email' => 'test@test.com',
            'email_verified_at' => now(),
            'verify' => now(),
            'password' => Hash::make('testtest'),
            'dial_code' => '+1',
            'phone' => '1234567890',
            'phone_number' => '+601234567890',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'point' => 100,
            'address1' => '123 Main St',
            'address2' => null,
            'address3' => null,
            'city' => 'Testville',
            'state' => 'TS',
            'zip' => '12345',
            'status' => 'active',
        ]);

        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);
    }
}
