<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DownloadController;
use App\Livewire\Public\FileBrowser;
use App\Livewire\Public\FileUpload;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FileManager;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\UserManager;

// ══════════════════════════════════════════════════════
// PUBLIC ROUTES
// ══════════════════════════════════════════════════════

Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::get('/browse', FileBrowser::class)->name('public.browse');
Route::get('/upload', FileUpload::class)->name('public.upload');

// File info page, download & preview
Route::get('/file/{uuid}',         [DownloadController::class, 'show'])->name('file.show');
Route::get('/file/{uuid}/download', [DownloadController::class, 'download'])->name('file.download');
Route::get('/file/{uuid}/preview',  [DownloadController::class, 'preview'])->name('file.preview');

// ══════════════════════════════════════════════════════
// AUTH ROUTES
// ══════════════════════════════════════════════════════

Route::get('/admin/login', function () {
    if (auth()->check()) return redirect()->route('admin.dashboard');
    return view('admin.login');
})->name('login');

Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $credentials['email'])->first();

    if (!$user || !\Hash::check($credentials['password'], $user->password)) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    if (!$user->is_active) {
        return back()->withErrors(['email' => 'Akun ini dinonaktifkan.'])->withInput();
    }

    auth()->login($user, $request->boolean('remember'));
    $request->session()->regenerate();
    $user->update(['last_login_at' => now()]);

    return redirect()->intended(route('admin.dashboard'));
})->name('admin.login.post');

Route::post('/admin/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('admin.logout');

// ══════════════════════════════════════════════════════
// ADMIN ROUTES (protected)
// ══════════════════════════════════════════════════════

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active_user'])->group(function () {

    Route::get('/dashboard',  Dashboard::class)->name('dashboard');
    Route::get('/files',      FileManager::class)->name('files');
    Route::get('/settings',   Settings::class)->name('settings');
    Route::get('/users',      UserManager::class)->name('users');

    // Admin download (tanpa is_public check)
    Route::get('/file/{uuid}/download', [DownloadController::class, 'adminDownload'])->name('file.download');
});
