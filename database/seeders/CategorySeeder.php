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
                ['name' => 'After Effects'],
                ['name' => 'Blender'],
                ['name' => 'Cinema 4D'],
                ['name' => 'VFX'],
                ['name' => '2D'],
                ['name' => '3D'],
                ['name' => 'Псевдо 3D'],
                ['name' => 'Проэкт'],
                ['name' => 'Принципы анимации'],
                ['name' => 'Скульптинг'],
                ['name' => 'Советы'],
            )
            ->create();
    }
}
