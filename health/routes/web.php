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
Route::get('/admin/assignRole', [\App\Http\Controllers\UserController::class, 'showUserForm'])->name('showUsers')->middleware('auth', 'role:admin');

Route::post('/admin/assignRole', [\App\Http\Controllers\UserController::class, 'updateUserRole'])->name('admin_change_role')->middleware('auth', 'role:admin');
Route::delete('/admin/deleteRole',[\App\Http\Controllers\UserController::class,'deleteUser'])->name('admin_delete_user')->middleware('auth', 'role:admin');


Route::get('/patient',[\App\Http\Controllers\PatientDoctorController::class,'allDoctors'])->name('showDoctors')->middleware('auth', 'role:patient');
Route::post('/patient/addDoctor',[\App\Http\Controllers\PatientDoctorController::class,'addDoctor'])->name('patient_add_doctor')->middleware('auth', 'role:patient');;