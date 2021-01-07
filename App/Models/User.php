<?php

namespace lumilock\lumilockToolsPackage\App\Models;

use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Laravel\Lumen\Auth\Authorizable;
use Throwable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, Authorizable;
    use Traits\UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login',
        'first_name',
        'last_name',
        'email',
        'active',
        'token',
        'expires_in',
        'token_type'
    ];

    // no updated_at, created_at
    public $timestamps = false;

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\Authenticatable::getAuthIdentifierName()
     */
    public function getAuthIdentifierName()
    {
        return 'first_name';
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\Authenticatable::getAuthIdentifier()
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    // public function getCustomDataAttribute($value)
    // {
    //     /**
    //      * First check if the custom_data exists in jwt payload
    //      * If not found, only because the jwt is going to be
    //      * generated next. Otherwise, it'll always be there.
    //      * And attacker cannot modify
    //      */
    //     try {
    //         return auth()->payload()->get('custom_data');
    //     } catch (Throwable $t) {
    //         // When generating the payload
    //         return $value;
    //     }
    // }

    // public function getJWTIdentifier()
    // {
    //     return $this->getKey();
    // }

    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }
}
