<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'=>'Admin',
            'email'=>'admin@gmail.com',
            'password' => Hash::make('admin',['rounds'=>12]),
            'role'=>'admin',
        ]);

        User::create([
            'name'=>'Maboul',
            'email'=>'drmaboul@gmail.com',
            'password' => Hash::make('maboul',['rounds'=>12]),
            'role'=>'doctor',
        ]);

        $user=User::create([
            'name'=>'Who',
            'email'=>'drwho@gmail.com',
            'password' => Hash::make('who',['rounds'=>12]),
            'role'=>'doctor',
        ]);

        $user_patient=User::create([
            'name'=>'Test',
            'email'=>'test@gmail.com',
            'password' => Hash::make('test',['rounds'=>12]),
            'role'=>'patient',
        ]);
        $doctor=Doctor::create([
            'user_id'=>$user->id,
        ]);

        Patient::create([
            'user_id'=>$user_patient->id,
            'doctor_id'=>$doctor->id,
        ]);  

    }
}
