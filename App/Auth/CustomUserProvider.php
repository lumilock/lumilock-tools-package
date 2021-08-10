<?php

namespace lumilock\lumilockToolsPackage\App\Auth;

use Carbon\Carbon;
use DateInterval;
use lumilock\lumilockToolsPackage\App\Models\User;
use Throwable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use lumilock\lumilockToolsPackage\App\Services\Contracts\TokenManager;

class CustomUserProvider implements UserProvider
{
    protected $session; // TODO check if we need session anymore
    protected $model;

    /**
     * TODO Check params $hash $session
     */
    public function __construct($hash, $model, $session)
    {
        $this->session = $session;
        $this->model = $model;
    }

    /**
     * TODO retreve a user by token
     * The retrieveByToken function retrieves a user by their unique $identifier and "remember me" $token,
     * stored in a field remember_token.
     * As with the previous method, the Authenticatable implementation should be returned.
     */
    public function retrieveByToken($identifier, $token)
    {
        throw new Throwable('Method not implemented.');
    }

    /**
     * ? search what is this function
     * The updateRememberToken method updates the $user field remember_token with the new $token.
     * The new token can be either a fresh token,
     * assigned on successful "remember me" login attempt,
     * or a null when user is logged out.
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new Throwable('Method not implemented.');
    }

    /**
     * TODO get an auth by id
     */
    public function retrieveById($identifier)
    {
        throw new Throwable('Method not implemented.');
        return $this->checkByToken($identifier);
    }

    /**
     * The retrieveByCredentials method receives the array of credentials passed to the Auth::attempt method
     * when attempting to sign into an application.
     * The method should then "query" the underlying persistent storage for the user matching those credentials.
     * Typically, this method will run a query with a "where" condition on $credentials['username'].
     * The method should then return an implementation of UserInterface.
     * This method should not attempt to do any password validation or authentication.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!$credentials || !$credentials['api_token']) {
            return null;
        }
        return $this->checkByToken($credentials['api_token']);
    }

    /**
     * The validateCredentials method should compare the given $user with the $credentials to authenticate the user.
     * For example, this method might compare the $user->getAuthPassword() string to a Hash::make of $credentials['password'].
     * This method should only validate the user's credentials and return boolean.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new Throwable('Method not implemented.');
        return true;
    }

    /**
     * answere : https://laracasts.com/discuss/channels/eloquent/making-a-laravel-54-query-on-a-json-field-containing-a-json-array
     * The checkTokenInDB method search if there already exist an user by api token in db, and update their tokens
     */
    public function checkTokenInDB($api_token)
    {
        // We remove Bearer string from token
        $token = str_replace("Bearer ", "", $api_token);
        // We check if an user exist with the current api_token
        $user = User::whereRaw('JSON_CONTAINS(tokens, \'{"token": "' . $token . '"}\')')->first();

        // If no user found we return the failed response of eloquent request
        if (!$user) {
            return $user;
        }

        $list_to_remove = []; // the list of token indexes to delete 
        // we iterate on each tokens of the user, and we get the index and the token infos
        foreach ($user->tokens as $index => $token_infos) {
            // we check if the token date has expired
            $token_has_expired = Carbon::now() > $token_infos['expires_at'];

            // if the token has expired we will remove it
            if ($token_has_expired) {
                // we add the index that we need to remove
                array_push($list_to_remove, $index);
            }
        }
        // if the are indexes in the list we remove there from the db
        if (count($list_to_remove) > 0) {
            // here we will remove the current api_token that has expired from the list of token
            $tokens_list = $user->tokens;
            // we create a new list of token without tokens which have index in the list of token indexes to delete 
            $tokens_list = array_values(array_diff_key($tokens_list, array_flip($list_to_remove)));
            $id = $user->id;
            // we update the data base
            User::where('id', $id)->update(['tokens' => $tokens_list]);
        }

        // If we have no token we will remove the user
        if (count($user->tokens) == 0) {
            User::findOrFail($user->id)->delete();
            return null;
        }
        // We check if an user exist always with the current api_token
        return User::whereRaw('JSON_CONTAINS(tokens, \'{"token": "' . $token . '"}\')')->first();
    }

    // https://code.tutsplus.com/fr/tutorials/how-to-create-a-custom-authentication-guard-in-laravel--cms-29667
    // https://dariopalladino.com/blog/coding/sso-authentication-with-laravel-5-3
    // https://code.tutsplus.com/fr/tutorials/how-to-create-a-custom-authentication-guard-in-laravel--cms-29667
    // https://medium.com/@sirajul.anik/api-gateway-authenticating-user-without-db-storage-in-laravel-lumen-3ef1c1f300d3
    // https://stackoverflow.com/questions/45024429/how-to-add-a-custom-user-provider-in-laravel-5-4
    // https://www.cloudways.com/blog/lumen-rest-api-authentication/
    // TODO check if this function is correct
    public function checkByToken($api_token)
    {
        // We check there already is an user in DB
        $user = $this->checkTokenInDB($api_token);

        // If we found a user we return it
        if ($user) {
            // TODO add in current $this->user
            return $user;
        }
        // if no users find by token (maybe the user exist but we can not find it by it's token)
        if (!$user) {

            // We send a request to the Auth service worker to ask if a user with this token exist
            $tokenManager = new TokenManager($api_token);
            $response = $tokenManager->checkToken();

            // Check if there is an error
            if (!$response) {
                return $response;
            }
            if ($response->getStatusCode() !== 200) {
                return $response->getReasonPhrase();
            }

            // Get the content of the response
            $body_resp = json_decode($response->getBody()->getContents())->data;
            // if we detect an error, we return the response
            if (isset($body_resp->Error)) {
                return $body_resp;
            }
            // we create a user and return it
            return $this->user = $this->createUser($this->model, $body_resp);
        }
        return "Unauthorized";
    }

    // protected function setUser($model)
    // {
    //     $user = User::create($model->toArray());
    //     return $user;
    // }
    /**
     * The createUser method create or update an user
     */
    protected function createUser($model_name, $body_resp)
    {
        // We get the user in db by it's id getting from the resquest
        $user = User::find($body_resp->user->id);
        // Checking if user exist in db
        if (!$user) {
            // if the user does not exist we will create it
            $time = Carbon::now(); // Date time now
            $time->add(new DateInterval('PT' . $body_resp->token_info->expires_in . 'S')); // We add the duration that left to our token
            $stamp = $time->format('Y-m-d H:i'); // Format conversion

            // we create a new user
            $model = app($model_name);
            $model->login = $body_resp->user->login;
            $model->first_name = $body_resp->user->first_name;
            $model->last_name = $body_resp->user->last_name;
            $model->email = $body_resp->user->email;
            $model->picture = $body_resp->user->picture;
            $model->active = $body_resp->user->active;
            $model->tokens = [['token' => $body_resp->token_info->token, 'expires_at' => $stamp, 'token_type' => $body_resp->token_info->token_type]];
            $model->save();
            $model->id = $body_resp->user->id; // we change the default id by the same id of the Auth web service app 
            $model->save();
            // $this->setRoles($model, $body_resp); // ! deprecated replace by field roles
            return $model;
        } else {
            // * If a user already exist we will just update his data
            // we update the user and push a new token in tokens list
            $user->id = $body_resp->user->id;
            $user->login = $body_resp->user->login;
            $user->first_name = $body_resp->user->first_name;
            $user->last_name = $body_resp->user->last_name;
            $user->email = $body_resp->user->email;
            $user->picture = $body_resp->user->picture;
            $user->active = $body_resp->user->active;

            $time = Carbon::now(); // Date time now
            $time->add(new DateInterval('PT' . $body_resp->token_info->expires_in . 'S')); // We add the duration that left to our token
            $stamp = $time->format('Y-m-d H:i'); // Format conversion

            // we add in db his new token
            $tokens_list = $user->tokens;
            array_push($tokens_list, ['token' => $body_resp->token_info->token, 'expires_at' => $stamp, 'token_type' => $body_resp->token_info->token_type]);
            $user->tokens = $tokens_list;
            $user->save();

            // we check again if all tokens are valid
            $userChecked = $this->checkTokenInDB($body_resp->token_info->token);
            return !$userChecked ? "Unauthorized" : $userChecked;
        }
    }
    // TODO implement roles
    protected function setRoles($model, $body)
    {
        $this->session->put('roles', $this->createRoles($model, $body));
        return $this;
    }
    // TODO implement roles
    protected function createRoles($user, $body)
    {
        return json_encode([
            'id' => $body->role->id,
            'user_id' => $user->id,
            'name' => $body->role->rolename,
        ]);
    }
}
