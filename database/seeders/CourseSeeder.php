<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::factory()
            ->has(Lesson::factory(5))
            ->hasAttached(Category::inRandomOrder()->limit(rand(3,5))->get())
            ->create([
                'cover' => '/cover-images/cover_img_1.webp',
                'visible' => true,
            ]);

        $course = Course::factory()
            ->hasAttached(Category::inRandomOrder()->limit(rand(3,5))->get())
            ->create([
                'cover' => '/cover-images/cover_img_1.webp',
                'visible' => true,
            ]);

        Unit::factory()
            ->count(3)
            ->hasAttached(Lesson::factory(rand(3,5)), ['course_id' => $course->id])
            ->create(['course_id' => $course->id]);
    }
}
