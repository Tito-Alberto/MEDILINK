<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\AdminPharmacyController;
use App\Http\Controllers\PharmacyOrderController;
use App\Http\Controllers\PharmacyProductController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

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

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/home', function () {
    return redirect()->route('home');
});

Route::get('/produtos', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/produtos/{product}', [StorefrontController::class, 'show'])->name('storefront.show');
Route::get('/farmacias', [StorefrontController::class, 'pharmacies'])->name('storefront.pharmacies');
Route::get('/farmacias/{pharmacy}', [StorefrontController::class, 'pharmacy'])->name('storefront.pharmacy');

Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrinho/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/carrinho', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
Route::get('/pedido/{order}', [CheckoutController::class, 'showOrder'])->name('orders.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/farmacia', [PharmacyController::class, 'status'])->name('pharmacy.status');
    Route::get('/farmacia/cadastro', [PharmacyController::class, 'create'])->name('pharmacy.create');
    Route::post('/farmacia/cadastro', [PharmacyController::class, 'store'])->name('pharmacy.store');
});

Route::middleware(['auth', 'pharmacy.approved'])->prefix('farmacia')->group(function () {
    Route::get('/produtos', [PharmacyProductController::class, 'index'])->name('pharmacy.products.index');
    Route::get('/produtos/criar', [PharmacyProductController::class, 'create'])->name('pharmacy.products.create');
    Route::post('/produtos', [PharmacyProductController::class, 'store'])->name('pharmacy.products.store');
    Route::get('/produtos/{product}/editar', [PharmacyProductController::class, 'edit'])->name('pharmacy.products.edit');
    Route::put('/produtos/{product}', [PharmacyProductController::class, 'update'])->name('pharmacy.products.update');
    Route::delete('/produtos/{product}', [PharmacyProductController::class, 'destroy'])->name('pharmacy.products.destroy');
    Route::get('/pedidos', [PharmacyOrderController::class, 'index'])->name('pharmacy.orders.index');
    Route::get('/pedidos/{order}', [PharmacyOrderController::class, 'show'])->name('pharmacy.orders.show');
    Route::post('/pedidos/{order}/nao-visto', [PharmacyOrderController::class, 'markUnseen'])->name('pharmacy.orders.unseen');
});

Route::middleware(['auth', 'can:admin'])->prefix('admin')->group(function () {
    Route::get('/farmacias', [AdminPharmacyController::class, 'index'])->name('admin.pharmacies.index');
    Route::post('/farmacias/{pharmacy}/approve', [AdminPharmacyController::class, 'approve'])->name('admin.pharmacies.approve');
    Route::post('/farmacias/{pharmacy}/reject', [AdminPharmacyController::class, 'reject'])->name('admin.pharmacies.reject');
    Route::delete('/farmacias/{pharmacy}', [AdminPharmacyController::class, 'destroy'])->name('admin.pharmacies.destroy');
});
