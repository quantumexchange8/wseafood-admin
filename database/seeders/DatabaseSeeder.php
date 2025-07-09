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
            'id_number' => 'MBR00001',
            'dial_code' => '+60',
            'phone' => '123456789',
            'phone_number' => '+60123456789',
            'dob' => '1990-01-01',
            'gender' => 'male',
            'point' => 100,
            'address1' => '123 Main St',
            'address2' => null,
            'address3' => null,
            'city' => 'Testville',
            'state' => 'TS',
            'zip' => '12345',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);
    }
}
