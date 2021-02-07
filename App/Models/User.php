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

    protected $table = 'users';

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
        'picture',
        'active',
        'tokens',
        'roles'
    ];

    protected $casts = [
        'tokens' => 'array',
        'roles' => 'array'
    ];

    // no updated_at, created_at
    public $timestamps = false;

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\Authenticatable::getAuthIdentifierName()
     * TODO check if we need to use id instead of first_name
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

    public function getAuthToken() {
        return 'tokens';
    }
    // public function getRememberToken();
    // public function setRememberToken($value);
    // public function getRememberTokenName();
}
