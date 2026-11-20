<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Menggunakan updateOrCreate agar tidak error jika seeder dijalankan ulang
        User::updateOrCreate(
            ['email' => env('SEED_EMAIL')], // Unik ID untuk pengecekan
            [
                'name' => env('SEED_NAME'),
                'password' => Hash::make(env('SEED_PASSWORD')),
                'is_admin' => true,
                'email_verified_at' => Carbon::now(), // Langsung aktif
            ]
        );

        $this->call(CategorySeeder::class);

        // Opsi: Jika ingin membuat beberapa user testing secara otomatis
        // User::factory(10)->create();
    }
}