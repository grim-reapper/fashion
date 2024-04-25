<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/shop/{category?}', [App\Http\Controllers\ShopController::class, 'index'])->name('shop');
Route::get('/detail/{product_id}', [App\Http\Controllers\ProductController::class, 'detail'])->name('detail');
Route::get('/add-to-cart', [App\Http\Controllers\CartController::class, 'index'])->name('add-to-cart');

Route::get('/test', function (){
    \DB::table('products')
        ->whereRaw('id % 3 = 0')
        ->update(['summary' => 'Sale']);
});
