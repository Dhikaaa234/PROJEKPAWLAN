<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sarana belajar',
            'Utilitas gedung',
            'Fasilitas umum',
            'Inventaris & bangunan',
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category],
                ['name' => $category]
            );
        }
    }
}
