<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLivewireController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\DeviceTokenController;

Route::prefix('admin')->name('admin.')->middleware('auth', 'adminauth')->group(function () {
    // dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    
    // Users (Livewire)
    Route::get('users/create', [AdminLivewireController::class, 'create'])->name('users.create');
    Route::get('users', [AdminLivewireController::class, 'users'])->name('user');
    Route::get('tags', [AdminLivewireController::class, 'tags'])->name('tag');
    Route::get('faqs', [AdminLivewireController::class, 'appFaq'])->name('faq');
    Route::get('withdraws', [AdminLivewireController::class, 'withdraw'])->name('withdraw');
    Route::get('posts', [AdminLivewireController::class, 'post'])->name('post');
    Route::get('setting', [AdminLivewireController::class, 'setting'])->name('setting');

     Route::post('/save-device-token', [DeviceTokenController::class, 'saveToken'])->name('save-device-token');
});