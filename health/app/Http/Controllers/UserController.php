<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
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
            return redirect()->back()->with('success', 'User role updated successfully!');
        } else {
            return redirect()->back()->with('error', 'You don\'t have the necessary permissions to perform this action.');
        }
    }

    public function deleteUser(Request $request)
    {
        $user = User::where('email', $request->post('user_email'))->firstOrFail();
        $user->delete();

        return redirect()->back()->with('success', 'The user has been deleted with success');
    }
}
