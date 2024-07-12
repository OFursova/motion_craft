<?php

use App\Filament\App\Resources\CourseResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(fn() => Filament::setCurrentPanel(
    Filament::getPanel('app'),
));

test('courses list page is not accessible for guest user', function () {
    get(CourseResource::getUrl('index'))->assertRedirect();
});

test('courses list page is rendered for user', function () {
    actingAs(User::factory()->create(['is_admin' => false]));

    get(CourseResource::getUrl('index'))->assertOk();
});

test('courses are listed for user', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $courses = Course::factory()
        ->count(3)
        ->has(Category::factory()->state(['name' => 'Test category']))
        ->create(['visible' => true]);

    $draftCourses = Course::factory()->count(2)->create(['visible' => false]);

    \Livewire\Livewire::actingAs($user)->test(CourseResource\Pages\ListCourses::class)
        ->assertOk()
        ->assertCanSeeTableRecords($courses)
        ->assertCanNotSeeTableRecords($draftCourses)
        ->assertCountTableRecords(3)
        ->assertSeeText($courses->first()->title)
        ->assertSeeText($courses->first()->overview)
        ->assertSee($courses->first()->level)
        ->assertSeeText('Test category')
        ->assertTableActionExists('view');
});

test('view page is opened after button is clicked', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $course = Course::factory()->create(['visible' => true]);

    \Livewire\Livewire::actingAs($user)->test(CourseResource\Pages\ListCourses::class)
        ->callTableAction('view', $course)
        ->assertHasNoTableActionErrors()
        ->assertSeeText($course->title);
});

test('user can see course details', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $course = Course::factory()
        ->has(Category::factory()->state(['name' => 'Test category']))
        ->create(['visible' => false]);

    Unit::factory()
        ->count(3)
        ->hasAttached(Lesson::factory(rand(3,5)), ['course_id' => $course->id])
        ->create(['course_id' => $course->id]);

    \Livewire\Livewire::actingAs($user)->test(CourseResource\Pages\ViewCourse::class, ['record' => $course->getRouteKey()])
        ->assertForbidden();

    $course->update(['visible' => true]);

    \Livewire\Livewire::actingAs($user)->test(CourseResource\Pages\ViewCourse::class, ['record' => $course->getRouteKey()])
        ->assertOk()
        ->assertSeeText($course->title)
        ->assertSeeText($course->overview)
        ->assertSeeText($course->description)
        ->assertSeeText('Test category')
        ->assertSeeText('Browse all courses')
        ->assertSeeText($course->units->count() . ' courses.units')
        ->assertSeeText($course->lessons->count() . ' courses.lessons')
        ->assertSeeText('Curriculum')
        ->assertSeeText($course->units->first()->title)
        ->assertSeeText($course->lessons->first()->title)
        ->assertSeeText(\App\Enums\DurationEnum::forHumans($course->lessons->first()->duration, true));
});

test('only authorized users can watch a course', function () {
})->todo();
