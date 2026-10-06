<?php

use App\Http\Controllers\Api\V1;
use App\Http\Controllers\Api\V1\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::middleware('throttle:api')->group(function (): void {
        Route::get('site', V1\SiteController::class)->name('site');
        Route::get('pages/{slug}', [V1\PageController::class, 'show'])->name('pages.show');
        Route::get('books', [V1\BookController::class, 'index'])->name('books.index');
        Route::get('books/{slug}', [V1\BookController::class, 'show'])->name('books.show');
        Route::get('book-categories', [V1\BookCategoryController::class, 'index'])->name('book-categories.index');
        Route::get('book-categories/{slug}', [V1\BookCategoryController::class, 'show'])->name('book-categories.show');
        Route::get('services', [V1\ServiceController::class, 'index'])->name('services.index');
        Route::get('sitemap', V1\SitemapController::class)->name('sitemap');
        Route::get('contact/options', [V1\ContactController::class, 'options'])->name('contact.options');
        Route::get('orders/{order}', [V1\OrderController::class, 'show'])->name('orders.show');
    });

    Route::post('contact', [V1\ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');
    Route::post('checkout', [V1\CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
    Route::post('orders/resend-downloads', [V1\OrderController::class, 'resend'])->middleware('throttle:forms')->name('orders.resend');
    Route::post('stripe/webhook', V1\StripeWebhookController::class)->name('stripe.webhook');
    Route::get('downloads/{orderItem}', [V1\DownloadController::class, 'show'])
        ->middleware(['signed', 'throttle:downloads'])
        ->name('downloads.show');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::post('auth/login', [Admin\AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

        Route::middleware(['auth:sanctum', 'ability:admin', 'throttle:admin-api'])->group(function (): void {
            Route::get('auth/me', [Admin\AuthController::class, 'me'])->name('auth.me');
            Route::post('auth/logout', [Admin\AuthController::class, 'logout'])->name('auth.logout');

            Route::post('books/{book}/publish', [Admin\BookController::class, 'publish'])->name('books.publish');
            Route::post('books/{book}/unpublish', [Admin\BookController::class, 'unpublish'])->name('books.unpublish');
            Route::apiResource('books', Admin\BookController::class);
            Route::apiResource('book-categories', Admin\BookCategoryController::class)->except('show');
            Route::apiResource('contact-messages', Admin\ContactMessageController::class)->only(['index', 'show', 'update']);
        });
    });
});
