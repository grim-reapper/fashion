<?php
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::group(['namespace' => 'Admin', 'as' => 'admin::', 'prefix' => 'admin'], function() {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('products', [ProductController::class, 'show'])->name('product.view');
    Route::get('product/create', [ProductController::class, 'create'])->name('product.create');
    Route::post('product/store', [ProductController::class, 'store'])->name('product.store');
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/shop/{category?}', [App\Http\Controllers\ShopController::class, 'index'])->name('shop');
Route::get('/detail/{product_id}', [App\Http\Controllers\ProductController::class, 'detail'])->name('detail');
Route::get('/add-to-cart', [App\Http\Controllers\CartController::class, 'index'])->name('add-to-cart');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact-save', [ContactController::class, 'contactSave'])->name('contact-save');

Route::get('/test', function (){
    \DB::table('products')
        ->whereRaw('id % 3 = 0')
        ->update(['summary' => 'Sale']);
});
