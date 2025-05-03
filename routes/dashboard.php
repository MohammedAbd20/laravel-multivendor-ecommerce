    <?php

use App\Http\Controllers\Dashboard\CategoriesDashboard;
use App\Http\Controllers\Dashboard\ProductsController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Middleware\CheckUserType;
use Illuminate\Support\Facades\Route;

// use Illuminate\Routing\Route;




Route::group([
    'middleware' => ['auth:admin'],
    'prefix' => "admin"
],function(){

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('dashboard.profile.destroy');

    Route::get('/categories/trash',[CategoriesDashboard::class,'trash'])->name("categories.trash");
    Route::get('/categories/{category}/restore',[CategoriesDashboard::class,'restore'])->name("categories.restore");
    Route::delete('/categories/{category}/force-delete',[CategoriesDashboard::class,'forceDelete'])->name("categories.force-delete");
    Route::delete('/categories/destroy-all', [CategoriesDashboard::class, 'destroyAll'])->name('categories.destroyAll');
    Route::delete('/categories/force-delete-all', [CategoriesDashboard::class, 'forceDeleteAll'])->name('categories.forceDeleteAll');
    Route::post('/categories/restore-all', [CategoriesDashboard::class, 'restoreAll'])->name('categories.restoreAll');
    Route::resource('categories',CategoriesDashboard::class);

    Route::get('/products/trash',[CategoriesDashboard::class,'trash'])->name("products.trash");
    Route::get('/products/{product}/restore',[CategoriesDashboard::class,'restore'])->name("products.restore");
    Route::delete('/products/{product}/force-delete',[CategoriesDashboard::class,'forceDelete'])->name("products.force-delete");
    Route::delete('/products/destroy-all', [CategoriesDashboard::class, 'destroyAll'])->name('products.destroyAll');
    Route::delete('/products/force-delete-all', [CategoriesDashboard::class, 'forceDeleteAll'])->name('products.forceDeleteAll');
    Route::post('/products/restore-all', [CategoriesDashboard::class, 'restoreAll'])->name('products.restoreAll');

});


