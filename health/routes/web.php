<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientDoctorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
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

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('/admin/assignRole', [UserController::class, 'showUserForm'])->name('showUsers')->middleware('auth', 'role:admin');

Route::post('/admin/assignRole', [UserController::class, 'updateUserRole'])->name('admin_change_role')->middleware('auth', 'role:admin');
Route::delete('/admin/deleteRole',[UserController::class,'deleteUser'])->name('admin_delete_user')->middleware('auth', 'role:admin');


Route::get('/patient',[PatientDoctorController::class,'allDoctors'])->name('showDoctors')->middleware('auth', 'role:patient');
Route::post('/patient/addDoctor',[PatientDoctorController::class,'addDoctor'])->name('patient_add_doctor')->middleware('auth', 'role:patient');;
Route::get('/patient/doctors', [PatientDoctorController::class, 'doctorsPatient'])->name('medicalRecord')->middleware('auth', 'role:patient');
Route::get('/doctor/patients',[DoctorController::class, 'patients'])->name('medicalRecordDoctor')->middleware('auth', 'role:doctor');
Route::delete('patient/removeDoctor',[PatientDoctorController::class,'removeDoctor'])->name('patient_remove_doctor')->middleware('auth', 'role:patient');
