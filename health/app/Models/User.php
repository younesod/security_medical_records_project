<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    private static $paths_to_private_key;
    public $incrementing = false;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'private_key',
        'public_key',
        'sign_public_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
   
    /**
     * Verify if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Verify if the user is a patient.
     */
    public function isPatient()
    {
        return $this->role === 'patient';
    }
    /**
     * Verify if the user is a doctor.
     */
    public function isDoctor()
    {
        return $this->role === 'doctor';
    }

    /**
     * Get the patient record associated with the user.
     */
    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * Get the doctor record associated with the user.
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->id = Str::uuid();
        });
    }

    /**
     * Generate and store a key pair for the user.
     *
     * @return void
     */
    public function generateAndStoreKeyPair()
    {
        self::$paths_to_private_key = env('PATH_TO_PRIVATE_KEY');
        $path = self::$paths_to_private_key. $this->email . '.pem';
        $commandPrivateKey = 'openssl genpkey -algorithm RSA -out ' . $path . ' -pkeyopt rsa_keygen_bits:2048';
        exec($commandPrivateKey);
        // // Chemin vers le fichier de clé publique
        $publicKeyPath = self::$paths_to_private_key . $this->email . '.pub';
        // // Exécution de la commande pour générer la clé publique
        $commandPublicKey='openssl rsa -in ' . $path . ' -pubout -out ' . $publicKeyPath;
        exec($commandPublicKey);
        $publicKeyContent = file_get_contents($publicKeyPath);
        $this->private_key = $path;
        $this->public_key = $publicKeyContent;
        //if there is a need to delete the publicKey saved locally
        // unlink($publicKeyPath);

        $this->save();
    }
}
