<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()
            ->count(11)
            ->sequence(
                ['title' => 'After Effects'],
                ['title' => 'Blender'],
                ['title' => 'Cinema 4D'],
                ['title' => 'VFX'],
                ['title' => '2D'],
                ['title' => '3D'],
                ['title' => 'Псевдо 3D'],
                ['title' => 'Проэкт'],
                ['title' => 'Принципы анимации'],
                ['title' => 'Скульптинг'],
                ['title' => 'Советы'],
            )
            ->create();
    }
}
