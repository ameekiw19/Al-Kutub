<?php

namespace Database\Seeders;

use App\Models\CategoryKatalog;
use Illuminate\Database\Seeder;

class CategoryKatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Aqidah', 'slug' => 'aqidah', 'sort_order' => 1],
            ['name' => 'Tauhid', 'slug' => 'tauhid', 'sort_order' => 2],
            ['name' => 'Fiqih', 'slug' => 'fiqih', 'sort_order' => 3],
            ['name' => 'Hadis', 'slug' => 'hadis', 'sort_order' => 4],
            ['name' => 'Tafsir', 'slug' => 'tafsir', 'sort_order' => 5],
            ['name' => 'Tasawuf', 'slug' => 'tasawuf', 'sort_order' => 6],
            ['name' => 'Sirah Nabawiyah', 'slug' => 'sirah', 'sort_order' => 7],
            ['name' => 'Bahasa Arab', 'slug' => 'bahasa-arab', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            CategoryKatalog::updateOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['is_active' => true])
            );
        }
    }
}
