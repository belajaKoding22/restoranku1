<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['category_name' => 'makanan', 'description' => 'Kategori Makanan'],
            ['category_name' => 'minuman', 'description' => 'Kategori Minuman'],
        ];

        DB::table('categories')->insert($categories);
    }
}
