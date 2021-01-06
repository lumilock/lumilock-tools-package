<?php

namespace lumilock\lumilockToolsPackage\Providers;

use lumilock\lumilockToolsPackage\App\Models\User;
use Throwable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomUserProvider implements UserProvider
{
    public function retrieveByToken ($identifier, $token) {
        throw new Throwable('Method not implemented.');
    }

    public function updateRememberToken (Authenticatable $user, $token) {
        throw new Throwable('Method not implemented.');
    }

    public function retrieveById ($identifier) {
        throw new Throwable('Method not implemented.');
        return $this->getMemberInstance($identifier);
    }

    public function retrieveByCredentials (array $credentials) {
        if (!$credentials) {
            return null;
        }
        dd($credentials);
        throw new Throwable('Method not implemented.');
        return $this->getMemberInstance($credentials['api_token']);
    }

    public function validateCredentials (Authenticatable $user, array $credentials) {
        throw new Throwable('Method not implemented.');
        return true;
    }
    /// https://dariopalladino.com/blog/coding/sso-authentication-with-laravel-5-3
    // https://code.tutsplus.com/fr/tutorials/how-to-create-a-custom-authentication-guard-in-laravel--cms-29667
    // https://medium.com/@sirajul.anik/api-gateway-authenticating-user-without-db-storage-in-laravel-lumen-3ef1c1f300d3
    // https://stackoverflow.com/questions/45024429/how-to-add-a-custom-user-provider-in-laravel-5-4
    // https://www.cloudways.com/blog/lumen-rest-api-authentication/
    private function getMemberInstance ($api_token) {
        throw new Throwable('Method not implemented.');
        dd(
         tap(new User(), function ($user) use ($api_token) {
            $user->id = $api_token;
            $user->api_token = (string) $api_token;
            // push whatever your require from user
            // Don't save the model instance here
            // As we won't use any stroage.
        }));
    }
}
