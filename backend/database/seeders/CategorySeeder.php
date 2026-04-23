<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['nama_kategori' => 'Sarana Belajar'],
            ['nama_kategori' => 'Utilitas Gedung'],
            ['nama_kategori' => 'Fasilitas Umum'],
            ['nama_kategori' => 'Inventaris & Bangunan'],
        ]);
    }
}
