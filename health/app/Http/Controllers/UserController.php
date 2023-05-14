<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return view('admin_user_form', ['users' => $users]);
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
            $user->role = $request->post('role');
            $user->save();
            if ($request->post('role') === 'doctor') {
                Doctor::create([
                    'user_id' => $user->id,
                ]);
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
        $user = User::where('email', $request->post('user_email'))->firstOrFail();
        $user->delete();
        //verifier si role docteur

        return redirect()->back()->with('success', 'The user has been deleted with success');
    }
}
