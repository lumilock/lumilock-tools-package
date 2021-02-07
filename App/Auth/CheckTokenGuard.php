<?php

namespace lumilock\lumilockToolsPackage\App\Auth;

use Illuminate\Auth\TokenGuard;
use lumilock\lumilockToolsPackage\App\Models\User;
use Throwable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use lumilock\lumilockToolsPackage\App\Services\Contracts\TokenManager;
use \Illuminate\Session\Store;

use Illuminate\Support\Str;

class CheckTokenGuard implements Guard
{
    protected $request;
    /**
     * The authentication guard factory instance.
     *
     * @var \lumilock\lumilockToolsPackage\App\Auth\CustomUserProvider
     */
    protected $provider;

    protected $user;

    /**
     * Create a new middleware instance.
     *
     * @param  \lumilock\lumilockToolsPackage\App\Auth\CustomUserProvider  $provider
     * @return void
     */
    public function __construct(CustomUserProvider $provider, Store $request)
    {
        $this->request = $request; // TODO remove session request if not needed
        $this->provider = $provider;
        $this->user = NULL;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // dd( $this->request);
        if (!is_null($this->user)) {
            return $this->user;
        }
    }

    /**
     * Get the JSON params from the current request
     *
     * @return string
     */
    public function getJsonParams()
    {
        dd('getJsonParams');
        $jsondata = $this->request->get('user_' . $this->user()->id); // TODO we do not have session anymore

        return (!empty($jsondata) ? json_decode($jsondata, TRUE) : NULL);
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return string|null
     */
    public function id()
    {
        if ($user = $this->user()) {
            return $this->user()->getAuthIdentifier(); // TODO check what is AuthIdentifier
        }
    }

    /**
     * Validate a user's credentials.
     * TODO refactor this function
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        dd('Validate');
        if (empty($credentials['username']) || empty($credentials['password'])) {
            if (!$credentials = $this->getJsonParams()) {
                return false;
            }
        }

        $user = $this->provider->retrieveByCredentials($credentials);

        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the current user.
     *
     * @param  Array $user User info
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * TODO add remember
     */
    public function login($user, $remember) {
        $this->setUser($user);
    }

    /**
     */
    public function attempt(array $credentials = [], $remember = false, $login = true)
    {
        // $this->fireAttemptEvent($credentials, $remember, $login);
        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'api_token') || !$value  || $value === "") {
                return false;
            }
        }
        $this->lastAttempted = $user = $this->provider->checkByToken($credentials['api_token']);

        // if there is an user and an error we return the content of the user error 
        if ($user && isset($user->Error))
            return $user;
        // checking if we have the 'Unauthorized' error
        if (!$user || $user === 'Unauthorized') {
            return false;
        }
        // login of the user
        if ($login) {
            $this->login($user, $remember);
            return $this->user;
        }
        return false;
    }
}
