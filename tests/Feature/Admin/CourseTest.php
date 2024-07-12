<?php

use App\Filament\Admin\Resources\CourseResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(fn() => Filament::setCurrentPanel(
    Filament::getPanel('admin'),
));

test('courses list page is not accessible for ordinary user', function () {
    actingAs(User::factory()->create(['is_admin' => false]));

    get(CourseResource::getUrl('index'))->assertForbidden();
});

test('courses list page is rendered for admin', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    get(CourseResource::getUrl('index'))->assertOk();
});

test('courses are listed', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $courses = Course::factory()->count(5)->create();

    \Livewire\Livewire::actingAs($admin)->test(CourseResource\Pages\ListCourses::class)
        ->assertCanSeeTableRecords($courses)
        ->assertCanRenderTableColumn('title')
        ->assertCanNotRenderTableColumn('cover')
        ->assertCanRenderTableColumn('level')
        ->assertCanNotRenderTableColumn('free')
        ->assertCanRenderTableColumn('visible')
        ->assertCanRenderTableColumn('categories.name')
        ->assertCanRenderTableColumn('created_at');
});

test('course can be created', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $newData = Course::factory()->make();

    \Livewire\Livewire::actingAs($admin)->test(CourseResource\Pages\CreateCourse::class)
        ->fillForm([
            'title' => $newData->title,
            //'cover' => $newData->cover,
            'overview' => $newData->overview,
            'description' => $newData->description,
            'level' => $newData->level,
            'free' => $newData->free,
            'visible' => $newData->visible,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Course::class, [
        'title' => $newData->title,
        //'cover' => $newData->cover,
        'overview' => $newData->overview,
        'description' => $newData->description,
        'level' => $newData->level,
        'free' => $newData->free,
        'visible' => $newData->visible,
    ]);
});

test('course details are shown', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $course = Course::factory()->create();

    \Livewire\Livewire::actingAs($admin)->test(CourseResource\Pages\EditCourse::class, [
        'record' => $course->getRouteKey(),
    ])
        ->assertFormSet([
            'title' => $course->title,
            'overview' => $course->overview,
            'description' => $course->description,
            'free' => $course->free,
            'visible' => $course->visible,
        ]);
});

test('course units can be shown', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $course = Course::factory()
        ->has(Unit::factory()->count(5))
        ->create();

    \Livewire\Livewire::actingAs($admin)->test(CourseResource\RelationManagers\UnitsRelationManager::class, [
        'ownerRecord' => $course,
        'pageClass' => CourseResource\Pages\EditCourse::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($course->units);
});


test('course lessons can be shown', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $course = Course::factory()
        ->has(Lesson::factory()->count(5))
        ->create();

    \Livewire\Livewire::actingAs($admin)->test(CourseResource\RelationManagers\LessonsRelationManager::class, [
        'ownerRecord' => $course,
        'pageClass' => CourseResource\Pages\EditCourse::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($course->lessons);
});
