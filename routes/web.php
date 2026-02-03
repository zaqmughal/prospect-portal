<?php

use App\Http\Controllers\ExportController;
use App\Models\LeadSource;
use App\Models\LeadSourceRun;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('settings', 'settings')->name('settings');

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
    Route::view('icps/{icp}/edit', 'icps.edit')->name('icps.edit');

    // Playbooks
    Route::view('playbooks', 'playbooks.index')->name('playbooks.index');
    Route::view('playbooks/create', 'playbooks.create')->name('playbooks.create');
    Route::view('playbooks/{playbook}/edit', 'playbooks.edit')->name('playbooks.edit');

    // Exports
    Route::get('exports/shortlist', [ExportController::class, 'shortlistCsv'])->name('exports.shortlist');
    Route::get('exports/outreach', [ExportController::class, 'outreachZip'])->name('exports.outreach');
});

require __DIR__.'/auth.php';
