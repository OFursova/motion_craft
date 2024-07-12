<?php

arch('app do not use debugging helpers')
    ->expect(['dd', 'dump'])
    ->not->toBeUsed();

arch('do not use env helper in code')
    ->expect(['env'])
    ->not->toBeUsed();

arch('controller classes should have proper suffix')
    ->expect('App\Controllers')
    ->toHaveSuffix('Controller');

arch('do not access session data in Async jobs')
    ->expect([
        'session',
        'auth',
        'request',
        'Illuminate\Support\Facades\Auth',
        'Illuminate\Support\Facades\Session',
        'Illuminate\Http\Request',
        'Illuminate\Support\Facades\Request',
    ])
    ->each->not->toBeUsedIn('App\Jobs');
