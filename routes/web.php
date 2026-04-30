<?php

use App\Http\Controllers\ExportController;
use App\Models\LeadSource;
use App\Models\LeadSourceRun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

// Invitation acceptance (accessible without auth)
Route::get('invitations/{token}', function (string $token) {
    return view('organizations.accept-invitation', ['token' => $token]);
})->name('invitations.accept');

Route::middleware(['auth'])->group(function () {
    Route::view('organizations/create', 'organizations.create')->name('organizations.create');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('settings', 'settings')->name('settings');
    Route::view('settings/team', 'settings.team')->name('settings.team');
    Route::view('settings/billing', 'settings.billing')->name('settings.billing');
    Route::view('settings/usage', 'settings.usage')->name('settings.usage');

    // Accounts
    Route::view('accounts', 'accounts.index')->name('accounts.index');
    Route::view('accounts/create', 'accounts.create')->name('accounts.create');
    Route::view('accounts/{account}', 'accounts.show')->name('accounts.show');
    Route::view('accounts/{account}/edit', 'accounts.edit')->name('accounts.edit');

    // Lead Sources (Discovery)
    Route::view('lead-sources', 'lead-sources.index')->name('lead-sources.index');
    Route::view('lead-sources/create', 'lead-sources.create')->name('lead-sources.create');
    Route::get('lead-sources/{leadSource}/edit', function (LeadSource $leadSource) {
        return view('lead-sources.edit', ['leadSource' => $leadSource]);
    })->name('lead-sources.edit');
    Route::get('lead-source-runs/{run}/candidates', function (LeadSourceRun $run) {
        return view('lead-source-runs.candidates', ['run' => $run]);
    })->name('lead-source-runs.candidates');

    // ICPs
    Route::view('icps', 'icps.index')->name('icps.index');
    Route::view('icps/create', 'icps.create')->name('icps.create');
    Route::view('icps/generate', 'icps.generate')->name('icps.generate');
    Route::view('icps/{icp}/edit', 'icps.edit')->name('icps.edit');

    // Playbooks
    Route::view('playbooks', 'playbooks.index')->name('playbooks.index');
    Route::view('playbooks/create', 'playbooks.create')->name('playbooks.create');
    Route::view('playbooks/generate', 'playbooks.generate')->name('playbooks.generate');
    Route::view('playbooks/{playbook}/edit', 'playbooks.edit')->name('playbooks.edit');

    // Exports
    Route::get('exports/shortlist', [ExportController::class, 'shortlistCsv'])->name('exports.shortlist');
    Route::get('exports/outreach', [ExportController::class, 'outreachZip'])->name('exports.outreach');
});

require __DIR__.'/auth.php';
