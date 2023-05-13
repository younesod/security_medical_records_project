<?php

// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class AuthController extends Controller
// {
//     public function register(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|string|email|unique:users|max:255',
//             'password' => 'required|string|confirmed|min:8',
//         ]);

//         $user = User::create([
//             'name' => $request->name,
//             'email' => $request->email,
//             'password' => bcrypt($request->password),
//             'role' => 'patient', // Set default role as 'patient'
//         ]);

//         Auth::login($user);

//         return view('register');
//     }

//     public function login(Request $request)
//     {
//         $credentials = $request->validate([
//             'email' => 'required|string|email|max:255',
//             'password' => 'required|string|min:8',
//         ]);

//         if (Auth::attempt($credentials)) {
//             return redirect('/');
//         }

//         return back()->withErrors([
//             'email' => 'The provided credentials do not match our records.',
//         ]);
//     }
//     public function logout(Request $request)
//     {
//         Auth::logout();
//         $request->session()->invalidate();
//         $request->session()->regenerateToken();
//         return redirect('/');
//     }
// }

