<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

use ParagonIE\Halite\KeyFactory;
use phpseclib3\Crypt\RSA;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    private static $paths_to_private_key;

    public function __construct()
    {
        self::$paths_to_private_key = env('PATH_TO_PRIVATE_KEY');
    }
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
     * Generate and store the key pair.
     *
     * @return string The public key.
     */
    public function generateAndStoreKeyPair()
    {

        $privateKey = RSA::createKey(2048);
        $this->private_key = $privateKey;
        $publicKey = $privateKey->getPublicKey();

        // Store the private key securely (example: store in the database)
        $this->public_key = $publicKey;
        $message = $this->email;
        $this->sign_public_key = $privateKey->sign($message);
        $this->save();

        // Return the public key for further use
        return $publicKey;
    }

    // public function generateEncryptionKeyPAirAndSignature(){
    //     $enc_keyPair= KeyFactory::generateEncryptionKeyPair();
    //     $sign_keyPair= KeyFactory::generateSignatureKeyPair();

    //     $privateKey= $enc_keyPair->getSecretKey();
    //     $private_key_sign=$sign_keyPair->getSecretKey();
    //     $public_key= $enc_keyPair->getPublicKey();
    //     $public_sign=$sign_keyPair->getPublicKey();


    //     $this->private_key=$privateKey;
    //     $path = base_path() . '/privateKeys/' .$this->email. '/';
    //     if(!is_dir($path)){
    //         mkdir($path,0777,true);
    //     }
    //     KeyFactory::save($privateKey,$path .'key.pem');
    //     $this->public_key=$public_key;
    //     $this->sign_public_key=$public_sign;
    //     $this->save();
    // }

    public function gen()
    {
        //create the keypair
        $config = array(
            'private_key_bits' => 2048, // Taille de la clé privée en bits
            'private_key_type' => OPENSSL_KEYTYPE_RSA, // Algorithme de chiffrement
        );

        $res = openssl_pkey_new($config);
        // get the privatekey
        openssl_pkey_export($res, $privatekey);
        $publickey = openssl_pkey_get_details($res);
        $publickey = $publickey['key'];
        $path = self::$paths_to_private_key. $this->email . '.pem';
        // openssl ecparam -name prime256v1 -genkey -noout -out be_ea_key.pem  
        // openssl ec -in be_ea_key.pem -pubout -out public_key.pem 
        // exec('openssl ecparam -name prime256v1 -genkey -noout -out '.$path);
        $commands = 'openssl genpkey -algorithm RSA -out ' . $path . ' -pkeyopt rsa_keygen_bits:2048';
        exec($commands);
        // // Chemin vers le fichier de clé publique
        $publicKeyPath = self::$paths_to_private_key . $this->email . '.pub';
        // dd($publicKeyPath);
        // // Exécution de la commande pour générer la clé publique
        // dd($publicKeyPath);
        // exec('openssl ec -in '.$path.' -pubout -out '.$publicKeyPath);
        exec('openssl rsa -in ' . $path . ' -pubout -out ' . $publicKeyPath);
        $publicKeyContent = file_get_contents($publicKeyPath);
        $this->private_key = $path;
        $this->public_key = $publicKeyContent;
        // unlink($publicKeyPath);

        $this->save();
    }
}
