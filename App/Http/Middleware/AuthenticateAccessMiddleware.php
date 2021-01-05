<?php

namespace lumilock\lumilockToolsPackage\App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use lumilock\lumilockToolsPackage\App\Traits\ApiResponser;

class AuthenticateAccessMiddleware
{
    
    use ApiResponser;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validSecrets = explode(',', env('ACCEPTED_SECRETS'));
        if(in_array($request->header('Authorization_secret'), $validSecrets))
        {
            return $next($request);
        }
        return $this->errorResponse('UNAUTHORIZED', Response::HTTP_UNAUTHORIZED);
    }
}
