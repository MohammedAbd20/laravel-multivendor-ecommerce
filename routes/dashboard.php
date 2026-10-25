    <?php

use App\Http\Controllers\Dashboard\CategoriesDashboard;
use App\Http\Controllers\Dashboard\AdminsController;
use App\Http\Controllers\Dashboard\CustomersController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\OrdersController;
use App\Http\Controllers\Dashboard\StoresController;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Middleware\CheckUserType;
use Illuminate\Support\Facades\Route;

// use Illuminate\Routing\Route;




Route::group([
    'middleware' => ['auth:admin'],
    'prefix' => "admin"
],function(){

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('dashboard.profile.destroy');

    Route::get('/categories/trash',[CategoriesDashboard::class,'trash'])->name("categories.trash");
    Route::post('/categories/{category}/restore',[CategoriesDashboard::class,'restore'])->name("categories.restore");
    Route::delete('/categories/{category}/force-delete',[CategoriesDashboard::class,'forceDelete'])->name("categories.force-delete");
    Route::delete('/categories/force-delete-all', [CategoriesDashboard::class, 'forceDeleteAll'])->name('categories.forceDeleteAll');
    Route::post('/categories/restore-all', [CategoriesDashboard::class, 'restoreAll'])->name('categories.restoreAll');
    Route::resource('categories',CategoriesDashboard::class);

    Route::get('/products/trash',[ProductsController::class,'trash'])->name("products.trash");
    Route::post('/products/{product}/restore',[ProductsController::class,'restore'])->name("products.restore");
    Route::delete('/products/{product}/force-delete',[ProductsController::class,'forceDelete'])->name("products.force-delete");
    Route::post('/products/bulk', [ProductsController::class, 'bulk'])->name('products.bulk');
    Route::delete('/products/force-delete-all', [ProductsController::class, 'forceDeleteAll'])->name('products.forceDeleteAll');
    Route::post('/products/restore-all', [ProductsController::class, 'restoreAll'])->name('products.restoreAll');
    Route::resource('products', ProductsController::class);

    Route::get('/orders/export', [OrdersController::class, 'export'])->name('orders.export');
    Route::post('/orders/bulk', [OrdersController::class, 'bulk'])->name('orders.bulk');
    Route::resource('orders', OrdersController::class)->only(['index', 'show', 'update']);

    Route::patch('/stores/{store}/status', [StoresController::class, 'status'])->name('stores.status');
    Route::resource('stores', StoresController::class);

    Route::resource('customers', CustomersController::class)->only(['index', 'show']);
    Route::resource('admins', AdminsController::class)->except(['show']);

});


