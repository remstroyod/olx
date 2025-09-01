<?php

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);
    $user->update([
        'email_verified_at' => now(),
    ]);
    return redirect()->route('dashboard')->with('status', 'Email successfully verified!');
})->name('verification.verify');

Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::post('/check-link', [\App\Http\Controllers\DashboardController::class, 'checkLink'])->middleware(['ajax'])->name('checkLink');
Route::post('/store', [\App\Http\Controllers\DashboardController::class, 'store'])->middleware(['ajax'])->name('subscribe.store');
