<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
     public function run(): void
    {
        // User::factory(10)->create();

         User::create([
            'name' => 'Admin',

            'password' => Hash::make('222222'),
            'phone' => '1000',
            'role' => 'admin'
        ]);

         User::create([
            'name' => 'Adminone',

            'password' => Hash::make('000000'),
            'phone' => '222',
            'role' => 'admin'
        ]);

         User::create([
            'name' => 'Seller',

            'password' => Hash::make('2000'),
            'phone' => '007',
            'role' => 'seller'
        ]);



    }
}
