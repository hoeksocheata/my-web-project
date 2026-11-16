<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WishlistController;

Route::get('/', function () {
    return redirect()->route('books.index');
});

// ផ្លូវសម្រាប់អ្នកមិនទាន់ Login (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.post');
});

// ផ្លូវដែលត្រូវបានការពារ (ទាមទារ Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // ព័ត៌មានគណនី (គ្រប់តួនាទី)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // លុបការវាយតម្លៃ (ម្ចាស់ ឬ Admin)
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // សម្រាប់ Admin ប៉ុណ្ណោះ (ត្រូវដាក់មុន books/{book} ដើម្បីកុំឱ្យ /books/create ច្រឡំជា {book})
    Route::middleware('admin')->group(function () {
        Route::get('/books/export', [BookController::class, 'export'])->name('books.export');
        Route::get('/books/import', [BookController::class, 'importForm'])->name('books.import');
        Route::post('/books/import', [BookController::class, 'import'])->name('books.import.store');
        Route::get('/books/import/template', [BookController::class, 'importTemplate'])->name('books.import.template');
        Route::resource('books', BookController::class)->except(['index', 'show']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // អ្នកផ្គត់ផ្គង់ និងការទិញចូល (បំពេញស្តុក)
        Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

        // ការលក់ និងវិក្កយបត្រ
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        // ការកម្មង់អនឡាញពីអតិថិជន (អនុម័ត / បដិសេធ)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
            Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
            Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/approve', [AdminOrderController::class, 'approve'])->name('orders.approve');
            Route::post('/orders/{order}/reject', [AdminOrderController::class, 'reject'])->name('orders.reject');
            Route::post('/orders/{order}/paid', [AdminOrderController::class, 'markPaid'])->name('orders.paid');
            Route::post('/orders/{order}/unpaid', [AdminOrderController::class, 'markUnpaid'])->name('orders.unpaid');
        });

        // របាយការណ៍
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // គ្រប់គ្រងអ្នកប្រើប្រាស់
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // សម្រាប់អតិថិជន (Customer) ប៉ុណ្ណោះ
    Route::middleware('customer')->group(function () {
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/{book}', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/{book}/buy-now', [CartController::class, 'buyNow'])->name('cart.buy-now');
        Route::patch('/cart/{book}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{book}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/payment', [OrderController::class, 'pay'])->name('orders.pay');

        Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/{book}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    });

    // អ្នកប្រើប្រាស់ទូទៅមើលបាន
    Route::resource('books', BookController::class)->only(['index', 'show']);
});
