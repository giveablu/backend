<?php

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaypalPayment;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\AccountController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\OtpVerifyController;
use App\Http\Controllers\Api\Other\SendDataController;
use App\Http\Controllers\Api\Auth\ForgotPassController; // 🎯 This handles password reset
use App\Http\Controllers\Api\Auth\AuthController; // 🎯 This handles general auth
use App\Http\Controllers\Api\Donor\DonorHomeController;
use App\Http\Controllers\Api\Admin\UserSocialController as AdminUserSocialController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Donor\DonorPaymentController;
use App\Http\Controllers\Api\Donor\DonorProfileController;
use App\Http\Controllers\Api\Donor\DonorDonationController;
use App\Http\Controllers\Api\Receiver\ReceiverHomeController;
use App\Http\Controllers\Api\Donor\DonorNotificationController;
use App\Http\Controllers\Api\Receiver\ReceiverBalanceController;
use App\Http\Controllers\Api\Receiver\ReceiverProfileController;
use App\Http\Controllers\Api\Receiver\ReceiverWithdrawController;
use App\Http\Controllers\Api\Receiver\ReceiverNotificationController;
use App\Http\Controllers\Api\Profile\LinkedSocialAccountController;
use App\Http\Controllers\Api\Profile\SocialAccountController as ProfileSocialAccountController;
use App\Http\Controllers\Api\ImpactController;
use App\Http\Controllers\Webhooks\MetaWebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Test routes to verify separation
    Route::get('test-auth', [AuthController::class, 'test']);
    Route::get('test-forgot', [ForgotPassController::class, 'test']);
    
    // Regular auth (handled by separate controllers)
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('verify-otp', [OtpVerifyController::class, 'verifyOtp']);
    Route::post('resend-otp', [OtpVerifyController::class, 'resendOtp']);
    Route::post('sign-in', [LoginController::class, 'login']);
    Route::post('social-login', [SocialLoginController::class, 'donorSocialLogin']);
    Route::post('social/{role}/{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::post('social/{role}/{provider}/callback', [SocialAuthController::class, 'callback']);
    
    // 🎯 PASSWORD RESET - All handled by ForgotPassController ONLY
    Route::post('forgot-password', [ForgotPassController::class, 'forgotPass']);
    Route::post('verify-reset-otp', [ForgotPassController::class, 'verifyResetOtp']);
    Route::post('reset-password', [ForgotPassController::class, 'resetPass']);

    // Receiver auth
    Route::prefix('receiver')->group(function () {
        Route::post('social-login', [SocialLoginController::class, 'SocialLogin']);
    });

    Route::post('switch-role', [AuthController::class, 'switchRole'])->middleware('auth:sanctum');

    // Logout
    Route::post('logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('delete/{id}', [AccountController::class, 'deleteAccount'])->middleware('auth:sanctum');
});

// Donor Account Routes
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'donor-account'], function () {
    // Donor Home
    Route::prefix('home')->group(function () {
        Route::get('/', [DonorHomeController::class, 'index']);
        Route::get('delete/{id}', [DonorHomeController::class, 'softDelete']);
    });

    // Donor Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [DonorProfileController::class, 'index']);
        Route::post('update', [DonorProfileController::class, 'update']);
    });

    // Donor Payment
    Route::prefix('payment')->group(function () {
        Route::post('/', [DonorPaymentController::class, 'success']);
    });

    // Donor Donations
    Route::prefix('donations')->group(function () {
        Route::get('/', [DonorDonationController::class, 'index']);
        Route::get('/{id}', [DonorDonationController::class, 'detail'])->name('donation.detail');
        Route::get('delete/{id}', [DonorDonationController::class, 'delete']);
    });

    // Donor Notification
    Route::prefix('notification')->group(function () {
        Route::get('list', [DonorNotificationController::class, 'list']);
        Route::post('remove', [DonorNotificationController::class, 'remove']);
    });
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('profile/social')->group(function () {
        Route::get('/', [ProfileSocialAccountController::class, 'index']);
        Route::post('{provider}/redirect', [LinkedSocialAccountController::class, 'redirect']);
        Route::post('{provider}/callback', [LinkedSocialAccountController::class, 'callback']);
        Route::delete('{social}', [ProfileSocialAccountController::class, 'destroy']);
    });

    Route::get('faqs', [DonorHomeController::class, 'faqs']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'receiver-account'], function () {
    Route::prefix('donation')->group(function () {
        Route::post('store/detail', [ReceiverHomeController::class, 'donationStore']);
        Route::post('store/bank', [ReceiverHomeController::class, 'BankStore']);
    });

    Route::prefix('home')->group(function () {
        Route::get('/', [ReceiverHomeController::class, 'index']);
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [ReceiverProfileController::class, 'index']);
        Route::post('update/detail', [ReceiverProfileController::class, 'detailUpdate']);
        Route::post('update/bank', [ReceiverProfileController::class, 'bankUpdate']);
        Route::post('update/post', [ReceiverProfileController::class, 'postUpdate']);
    });

    Route::prefix('balance')->group(function () {
        Route::get('/', [ReceiverBalanceController::class, 'index']);
    });

    Route::prefix('withdraw')->group(function () {
        Route::post('create', [ReceiverWithdrawController::class, 'create']);
    });

    Route::prefix('notification')->group(function () {
        Route::get('list', [ReceiverNotificationController::class, 'list']);
        Route::post('remove', [ReceiverNotificationController::class, 'remove']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('post/detail/{id}', [DonorHomeController::class, 'detail']);
});

Route::get('impact/estimate', [ImpactController::class, 'estimate']);

Route::get('country-list', [SendDataController::class, 'sendCountry']);
Route::get('tags', [SendDataController::class, 'sendTag']);
Route::get('paypal/success', [PaypalPayment::class, 'success']);
Route::get('paypal/cancel', [PaypalPayment::class, 'cancel']);

// PayPal Order Route
use App\Http\Controllers\PaypalController;
Route::post('/paypal/create-order', [PaypalController::class, 'createOrder']);
Route::post('/paypal/capture-order', [PaypalController::class, 'captureOrder']);

Route::prefix('meta')->group(function () {
    Route::get('webhook', [MetaWebhookController::class, 'verify']);
    Route::post('webhook', [MetaWebhookController::class, 'handle']);
});

Route::middleware(['auth:sanctum', 'adminauth'])->prefix('admin')->group(function () {
    Route::get('users/{user}/social', [AdminUserSocialController::class, 'index']);
    Route::patch('users/{user}/social/{social}', [AdminUserSocialController::class, 'update']);
});
