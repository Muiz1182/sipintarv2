<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        $categories = [
            'UMUM',
            'SMART GRUP',
            'KELAS 10',
            'KELAS 11',
            'KELAS 12',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category), // Membuat format 'smart-grup'
            ]);
        }
    }
}