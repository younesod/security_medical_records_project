<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use phpseclib3\Crypt\RSA;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $privateKey = RSA::createKey(2048);
        // $publicKey = $privateKey->getPublicKey();
        // // Supprimer les en-têtes et les pieds de page de la clé publique
        // $publicKey = preg_replace('/\R/', '', $publicKey);
        // $publicKey = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'], '', $publicKey);


        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin', ['rounds' => 12]),
            'role' => 'admin',
        ]);
        $user->gen();

        $doc = User::create([
            'name' => 'Maboul',
            'email' => 'drmaboul@gmail.com',
            'password' => Hash::make('maboul', ['rounds' => 12]),
            'role' => 'doctor',
        ]);
        $doc->gen();

        $user = User::create([
            'name' => 'Who',
            'email' => 'drwho@gmail.com',
            'password' => Hash::make('who', ['rounds' => 12]),
            'role' => 'doctor',
        ]);
        $user->gen();

        $user_patient = User::create([
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'password' => Hash::make('test', ['rounds' => 12]),
            'role' => 'patient',
        ]);
        $user_patient->gen();
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
