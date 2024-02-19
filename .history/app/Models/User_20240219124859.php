<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'password' => 'hashed',
    ];
             /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */

     protected static $rulesLogin = [
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:8|max:24'
    ];

       /**
     * The function returns the validation rules for login in PHP.
     *
     * @return The method `getValidationRulesLogin()` is returning the value of the static property
     * ``.
     */
    public static function getValidationRulesLogin()
    {
        return self::$rulesLogin;
    }

    public function getImageAttribute(){
        if (!empty($this->attributes['image'])) {
            if (file_exists(public_path().$this->attributes['image'])) {
                return $this->attributes['image'];
            }else{
                return $this->attributes['image'];
            }
        }
        return 'dashboard/images/img1.jpg';
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
