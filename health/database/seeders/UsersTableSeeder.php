<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);
        $user->generateAndStoreKeyPair();

        $doc = User::create([
            'name' => 'Maboul',
            'email' => 'drmaboul@gmail.com',
            'password' => Hash::make('maboul'),
            'role' => 'doctor',
        ]);
        $doc->generateAndStoreKeyPair();

        $user = User::create([
            'name' => 'Who',
            'email' => 'drwho@gmail.com',
            'password' => Hash::make('who'),
            'role' => 'doctor',
        ]);
        $user->generateAndStoreKeyPair();

        $user_patient = User::create([
            'name' => 'Leeroy Jenkins',
            'email' => 'test@gmail.com',
            'password' => Hash::make('test'),
            'role' => 'patient',
        ]);
        $user_patient->generateAndStoreKeyPair();
        $doctor = Doctor::create([
            'user_id' => $user->id,
        ]);

        Doctor::create([
            'user_id' => $doc->id,
        ]);

        $patient = Patient::create([
            'user_id' => $user_patient->id,
        ]);

    }
}
