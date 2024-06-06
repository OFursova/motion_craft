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
            ->count(6)
            ->sequence(
                ['name' => 'After Effects'],
                ['name' => 'Blender'],
                ['name' => 'Cinema 4D'],
                ['name' => 'Live Project'],
                ['name' => 'Principles of Animation'],
                ['name' => 'Tips'],
            )
            ->create();
    }
}
