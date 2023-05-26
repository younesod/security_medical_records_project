<?php

use App\Http\Controllers\ConsentRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientDoctorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientRecordController;
use App\Http\Controllers\DoctorRecordController;
use App\Http\Controllers\HomeController;
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

//admin
Route::get('/admin/assignRole', [UserController::class, 'showUserForm'])->name('showUsers')->middleware('auth', 'role:admin');
Route::post('/admin/assignRole', [UserController::class, 'updateUserRole'])->name('admin_change_role')->middleware('auth', 'role:admin');
Route::delete('/admin/deleteRole',[UserController::class,'deleteUser'])->name('admin_delete_user')->middleware('auth', 'role:admin');



//patient
Route::get('/notification',[ConsentRequestController::class,'showRequestsDoctor'])->name('consent_request')->middleware('auth', 'role:patient');
Route::get('/patient',[PatientDoctorController::class,'allDoctors'])->name('showDoctors')->middleware('auth', 'role:patient');
Route::get('/dossier',[PatientRecordController::class,'allRecord'])->name('showRecord')->middleware('auth', 'role:patient');
Route::get('/patient/download/{id}', [PatientRecordController::class, 'download'])->name('patient_download')->where('id', '[0-9]+')->middleware('auth', 'role:patient');
Route::get('/patient/doctors', [PatientDoctorController::class, 'doctorsPatient'])->name('medicalRecord')->middleware('auth', 'role:patient');

Route::delete('/patient/deleteDoctor',[PatientDoctorController::class,'deleteDoctor'])->name('patient_delete_doctor')->middleware('auth', 'role:patient');
Route::delete('/patient/deleteRecord',[PatientRecordController::class,'deleteFile'])->name('patient_delete_file')->middleware('auth', 'role:patient');
Route::delete('patient/removeDoctor',[PatientDoctorController::class,'removeDoctor'])->name('patient_remove_doctor')->middleware('auth', 'role:patient');

Route::post('/patient/createRecord',[PatientRecordController::class,'createFile'])->name('patient_create_file')->middleware('auth', 'role:patient');
Route::post('/patient/addDoctor',[PatientDoctorController::class,'addDoctor'])->name('patient_add_doctor')->middleware('auth', 'role:patient');;
Route::post('/consent/request/response',[ConsentRequestController::class,'processConsentRequest'])->name('process_consent_request')->middleware('auth', 'role:patient');

//doctor

Route::get('/doctor/patient',[PatientDoctorController::class,'showPatient'])->name('showPatients')->middleware('auth', 'role:doctor');
Route::get('/doctor/patients',[DoctorController::class, 'patients'])->name('medicalRecordDoctor')->middleware('auth', 'role:doctor');
Route::get('/doctor/dossier',[DoctorRecordController::class,'showRecordDoctor'])->name('showRecordDoctor')->middleware('auth', 'role:doctor');


Route::delete('/doctor/dossier/detail/delete', [DoctorRecordController::class, 'deleteRecordOfPatient'])->name('doctor_delete_file')->middleware('auth', 'role:doctor');
Route::delete('/doctor/deletePatient',[PatientDoctorController::class,'deletePatient'])->name('doctor_delete_patient')->middleware('auth', 'role:doctor');
Route::delete('/removePatient',[DoctorController::class,'removePatient'])->name('remove_patient')->middleware('auth','role:doctor');

Route::post('/consent/request/add',[ConsentRequestController::class,'addPatient'])->name('request_add_patient')->middleware('auth', 'role:doctor');
Route::post('/doctor/addPatient',[PatientDoctorController::class,'addPatient'])->name('doctor_add_patient')->middleware('auth', 'role:doctor');
Route::post('/doctor/dossier/detail/add', [DoctorRecordController::class, 'addRecordOfPatient'])->name('doctor_add_file')->middleware('auth', 'role:doctor');
Route::post('/doctor/dossier/detail/download', [DoctorRecordController::class, 'download'])->name('doctor_download')->middleware('auth', 'role:doctor');
Route::match(['post','get'],'/doctor/dossier/detail', [DoctorRecordController::class, 'showRecordOfPatient'])->name('doctor.dossierFile')->middleware('auth', 'role:doctor');