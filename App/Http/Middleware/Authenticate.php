<?php

namespace lumilock\lumilockToolsPackage\App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Auth\Factory as Auth;
// use Illuminate\Contracts\Auth\Guard as Guard;
// use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $credentials['api_token'] = $request->header('Authorization');
        if ($user = $this->auth->guard($guard)->attempt($credentials, false)) {
            if ($user && isset($user->Error))
                return response('Unauthorized.', 401);
            return $next($request, $guard);
        }
        return response('Unauthorized.', 401);
    }
}
