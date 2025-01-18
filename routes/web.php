<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return redirect()->route('projects.index');
})->middleware('auth');
// Route::get('/', \App\Livewire\Dashboard::class)->middleware('auth');

Route::get('/main', \App\Livewire\Dashboard::class)->name('main');

Route::get('/auth/github/callback', [\App\Http\Controllers\GitHubController::class, 'handleGitHubCallback']);

//Route::get('/github/connect', function () {
//    return redirect()->to('https://github.com/apps/hubbub-the-missing-front-end/installations/new');
//})->name('github.connect');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');

    Route::get('/projects', \App\Livewire\ProjectSelector::class)
        ->name('projects.index');

    Route::get('/projects/{project}/settings/github-connect', \App\Livewire\ConnectGitHubRepo::class)
        ->name('projects.settings.github-connect');

    Route::get('/projects/{project}/settings/{tab?}', \App\Livewire\ProjectSettings::class)
        ->name('projects.settings');

    Route::get('/projects/{project}/settings/github/connect', [\App\Livewire\ProjectSettings::class, 'connectGitHubAccount'])
        ->name('projects.settings.github-connect');

    Route::get('/projects/{project}', \App\Livewire\Dashboard::class)
        ->name('projects.show');

    //    Route::get('/dashboard', function () {
    //        return view('dashboard');
    //    })->name('dashboard');
});
