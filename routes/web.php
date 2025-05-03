<?php

// use App\Http\Controllers\Dashboard\CategoriesDashboard;

use App\Http\Controllers\Front\Auth\TwoFAController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\CurrencyConverterController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class,'index'])->name('home');
// Route::get('/', function(){
//     return view('welcome');
// });

Route::get('/productsF',[ProductController::class,'index'])->name('productsF.index');
Route::get('/productsF/{product:slug}',[ProductController::class,'show'])->name('productsF.show');

Route::resource('cart', CartController::class);

Route::get('checkout',[CheckoutController::class,'create'])->name('checkout');
Route::post('checkout',[CheckoutController::class,'store'])->name('checkout.store');


Route::middleware('auth:admin')->group(function () {
    Route::get('profile',[ProfileController::class , 'edit'])->name('front.profile.edit');
    Route::patch('profile',[ProfileController::class , 'update'])->name('front.profile.update');
});


Route::get('/auth/user/2fa',[TwoFAController::class,'index'])->name('front.2fa');


Route::post('currency', [CurrencyConverterController::class , 'store' ])->name('currency.store');

// require __DIR__.'/auth.php';
require __DIR__. '/dashboard.php';
