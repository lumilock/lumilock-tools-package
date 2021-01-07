<?php

namespace lumilock\lumilockToolsPackage\App\Auth;

use lumilock\lumilockToolsPackage\App\Models\User;
use Throwable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use lumilock\lumilockToolsPackage\App\Services\Contracts\TokenManager;

class CustomUserProvider implements UserProvider
{
    protected $session;

    public function __construct($hash, $model, $session)
    {
        $this->session = $session;
    }

    public function retrieveByToken($identifier, $token)
    {
        throw new Throwable('Method not implemented.');
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new Throwable('Method not implemented.');
    }

    public function retrieveById($identifier)
    {
        throw new Throwable('Method not implemented.');
        return $this->checkByToken($identifier);
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!$credentials) {
            return null;
        }
        dd($credentials);
        return $this->checkByToken($credentials['api_token']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new Throwable('Method not implemented.');
        return true;
    }
    /// https://dariopalladino.com/blog/coding/sso-authentication-with-laravel-5-3
    // https://code.tutsplus.com/fr/tutorials/how-to-create-a-custom-authentication-guard-in-laravel--cms-29667
    // https://medium.com/@sirajul.anik/api-gateway-authenticating-user-without-db-storage-in-laravel-lumen-3ef1c1f300d3
    // https://stackoverflow.com/questions/45024429/how-to-add-a-custom-user-provider-in-laravel-5-4
    // https://www.cloudways.com/blog/lumen-rest-api-authentication/
    public function checkByToken($api_token)
    {
        $tokenManager = new TokenManager($api_token);
        $response = $tokenManager->checkToken();

        // Check if there is an error
        if ($response->getStatusCode() !== 200)
            return $response->getReasonPhrase();

        // Get the content of the response
        $body_resp = json_decode($response->getBody()->getContents())->data;
        if (isset($body_resp->Error))
            return $body_resp;
        // $this->setResponseUser($body_resp);
        // dd($this->createUser(new User(), $body_resp));
        return $this->user = $this->createUser(new User(), $body_resp);
        throw new Throwable('Method not implemented.');
        // dd(
        //     tap(new User(), function ($user) use ($api_token) {
        //         $user->id = $api_token;
        //         $user->api_token = (string) $api_token;
        //         // push whatever your require from user
        //         // Don't save the model instance here
        //         // As we won't use any stroage.
        //     })
        // );
    }

    protected function setUser($model)
    {
        $this->session->put('users', $model);
        return $this;
    }
    protected function createUser($model, $body_resp)
    {
        $model->id = $body_resp->user->id;
        $model->login = $body_resp->user->login;
        $model->first_name = $body_resp->user->first_name;
        $model->last_name = $body_resp->user->last_name;
        $model->email = $body_resp->user->email;
        $model->picture = $body_resp->user->picture;
        $model->active = $body_resp->user->active;
        $model->token = $body_resp->token_info->token;
        $model->expires_in = $body_resp->token_info->expires_in;
        $model->token_type = $body_resp->token_info->token_type;
        $this->setUser($model);
        // $this->setRoles($model, $body_resp);
        return $model;
    }
    protected function setRoles($model, $body)
    {
        $this->session->put('roles', $this->createRoles($model, $body));
        return $this;
    }
    protected function createRoles($user, $body)
    {
        return json_encode([
            'id' => $body->role->id,
            'user_id' => $user->id,
            'name' => $body->role->rolename,
        ]);
    }
}
