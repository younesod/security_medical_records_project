<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    if (Auth::check()) {
        return view('/home');
    } else {
        return view('auth/login');
    }
    // return view('/home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('/admin/assignRole', [\App\Http\Controllers\UserController::class, 'showUserForm'])->name('showUsers');

Route::post('/admin/assignRole', [\App\Http\Controllers\UserController::class, 'updateUserRole'])->name('admin_change_role');
Route::delete('/admin/deleteRole',[\App\Http\Controllers\UserController::class,'deleteUser'])->name('admin_delete_user');
