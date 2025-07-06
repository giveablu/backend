<?php

use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;

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

Route::redirect('/', 'admin/dashboard')->name('home');

Route::view('api-doc', 'swagger');

Route::get('contact-us', [ContactUsController::class, 'contactUs'])->name('contact-us');

Route::post('/store', [ContactUsController::class, 'store']);

Route::get('test-mail', function () {
    $res = Mail::to('lifeofsroy@gmail.com')->send(new TestMail());

    dd($res);
});


use Illuminate\Support\Facades\Artisan;

