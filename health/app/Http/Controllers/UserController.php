<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpParser\Comment\Doc;

class UserController extends Controller
{
    /**
     * Show the user form for an admin to manage users.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showUserForm()
    {
        $users = User::all()->except(Auth::id());
        return view('admin.admin_user_form', ['users' => $users]);
    }
    /**
     * Check if the logged in user is an admin.
     *
     * @return bool
     */
    protected function isAdmin()
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    /**
     * Update the role of a user.
     *
     * @param  Request  $request
     * @return 
     */
    public function updateUserRole(Request $request)
    {
        if ($this->isAdmin()) {
            $user = User::find($request->post('user_id'));
            $role = $request->post('role');
            $user->role = $role;
            $user->save();
            switch ($role) {
                case 'doctor':
                    $patient = Patient::where('user_id', $user->id)->first();
                    if ($patient) {
                        $doctorAssociate = DoctorPatient::where('patient_id', $patient->patient_id)->get();
                        if ($doctorAssociate->count() > 0) {
                            foreach ($doctorAssociate as $doctor) {
                                $doctorData = Doctor::where('doctor_id', $doctor->doctor_id)->firstOrFail();
                                $files = MedicalRecord::where('user_id', $user->id)->get();
                                foreach ($files as $file) {
                                    Storage::delete('public/medical_records/' . $file->name . $doctorData->user->email . '.key');
                                    Storage::delete('public/medical_records/' . $file->name . '.bin');
                                    Storage::delete('public/medical_records/' . $file->name . '.iv');
                                    Storage::delete('public/medical_records/' . $file->name . $user->email . '.key');
                                }
                                $files->delete();
                            }
                        } else {
                            $files = MedicalRecord::where('user_id', $user->id)->get();
                            foreach ($files as $file) {
                                Storage::delete('public/medical_records/' . $file->name . '.bin');
                                Storage::delete('public/medical_records/' . $file->name . '.iv');
                                Storage::delete('public/medical_records/' . $file->name . $user->email . '.key');
                                $file->delete();
                            }
                        }
                        $patient->delete();
                    }
                    if (!Doctor::where('user_id', $user->id)->exists()) {
                        Doctor::create([
                            'user_id' => $user->id,
                        ]);
                    }
                    break;
                case 'patient':
                    $doctor = Doctor::where('user_id', $user->id)->first();
                    if ($doctor) {
                        $doctorAssociate = DoctorPatient::where('doctor_id', $doctor->doctor_id)->get();
                        if ($doctorAssociate->count() > 0) {
                            foreach ($doctorAssociate as $doctorData) {
                                $files = MedicalRecord::where('user_id', $doctorData->patient->user->id)->get();
                                foreach ($files as $file) {
                                    Storage::delete('public/medical_records/' . $file->name . $doctorData->doctor->user->email . '.key');
                                }
                            }
                        }
                        $doctor->delete();
                    }
                    if (!Patient::where('user_id', $user->id)->exists()) {
                        Patient::create([
                            'user_id' => $user->id,
                        ]);
                    }
                    break;
                case 'admin':
                    $doctor = Doctor::where('user_id', $user->id)->first();
                    $patient = Patient::where('user_id', $user->id)->first();
                    if ($doctor) $doctor->delete();
                    else if ($patient) $patient->delete();
                    break;
                default:
                    return redirect()->back()->with('error', 'You picked the wrong role,fool!');
            }
            return redirect()->back()->with('success', 'User role updated successfully!');
        } else {
            return redirect()->back()->with('error', 'You don\'t have the necessary permissions to perform this action.');
        }
    }

    /**
     * Delete a user and their associated records from the database.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteUser(Request $request)
    {
        $user = User::where('id', $request->post('user_id'))->firstOrFail();
        if ($user->isDoctor()) {
            $doc = Doctor::where('user_id', $user->id)->firstOrFail();
            $doctorAssociate = DoctorPatient::where('doctor_id', $doc->doctor_id)->get();
            foreach ($doctorAssociate as $doctor) {
                $doctorData = Doctor::where('doctor_id', $doctor->doctor_id)->firstOrFail();
                $patient = Patient::where('patient_id', $doctor->patient_id)->firstOrFail();
                $files = MedicalRecord::where('user_id', $patient->user->id)->get();
                foreach ($files as $file) {
                    Storage::delete('public/medical_records/' . $file->name . $doctorData->user->email . '.key');
                }
            }
        }
        if ($user->isPatient()) {
            $patient = Patient::where('user_id', $user->id)->first();
            $doctorAssociate = DoctorPatient::where('patient_id', $patient->patient_id)->get();
            if ($doctorAssociate->count() > 0) {
                foreach ($doctorAssociate as $doctor) {
                    $doctorData = Doctor::where('doctor_id', $doctor->doctor_id)->firstOrFail();
                    $files = MedicalRecord::where('user_id', $user->id)->get();
                    foreach ($files as $file) {
                        Storage::delete('public/medical_records/' . $file->name . $doctorData->user->email . '.key');
                        Storage::delete('public/medical_records/' . $file->name . '.bin');
                        Storage::delete('public/medical_records/' . $file->name . '.iv');
                        Storage::delete('public/medical_records/' . $file->name . $user->email . '.key');
                    }
                }
            } else {
                $files = MedicalRecord::where('user_id', $user->id)->get();
                if ($files) {
                    foreach ($files as $file) {
                        Storage::delete('public/medical_records/' . $file->name . '.bin');
                        Storage::delete('public/medical_records/' . $file->name . '.iv');
                        Storage::delete('public/medical_records/' . $file->name . '.key');
                    }
                }
            }
        }
        $user->delete();
        return redirect()->back()->with('success', 'The user has been deleted with success');
    }
}
