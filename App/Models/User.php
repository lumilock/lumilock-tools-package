<?php

namespace lumilock\lumilockToolsPackage\App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Throwable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements JWTSubject, Authenticatable
{
    use AuthenticatableTrait; // Laravel provided trait
    public function getCustomDataAttribute ($value) {
        /**
         * First check if the custom_data exists in jwt payload
         * If not found, only because the jwt is going to be
         * generated next. Otherwise, it'll always be there.
         * And attacker cannot modify
         */
        try {
            return auth()->payload()->get('custom_data');
        } catch ( Throwable $t ) {
            // When generating the payload
           return $value;
        }
    }

    public function getJWTIdentifier () {
        return $this->getKey();
    }

    public function getJWTCustomClaims () {
        return [ 
            'custom_data' => $this->custom_data,
        ];
    }
}