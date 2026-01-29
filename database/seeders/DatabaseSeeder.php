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
            ['email' => 'adminsipintar@gmail.com'], // Unik ID untuk pengecekan
            [
                'name' => 'Super Admin SIPINTAR',
                'password' => Hash::make('admin1182'),
                'email_verified_at' => Carbon::now(), // Langsung aktif
            ]
        );

        // Opsi: Jika ingin membuat beberapa user testing secara otomatis
        // User::factory(10)->create();
    }
}