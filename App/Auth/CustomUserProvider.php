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
        return $this->checkByToken($credentials['api_token']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new Throwable('Method not implemented.');
        return true;
    }

    /**
     * 
     * answere : https://laracasts.com/discuss/channels/eloquent/making-a-laravel-54-query-on-a-json-field-containing-a-json-array
     */
    public function checkTokenInDB($api_token)
    {
        // We remove Bearer string from token
        $token = str_replace("Bearer ", "", $api_token);
        // We check if an user exist with the current api_token
        $user = User::whereRaw('JSON_CONTAINS(tokens, \'{"token": "' . $token . '"}\')')->first();

        if (!$user) {
            return $user;
        }


        $list_to_remove = [];
        foreach ($user->tokens as $index => $token_infos) {
            // we check if the token date has expired
            $token_has_expired = Carbon::now() > $token_infos['expires_at'];

            // if the token has expired we will remove it
            if ($token_has_expired) {
                // we add the index that we need to remove
                array_push($list_to_remove, $index);
                
            }
        }
        if (count($list_to_remove) > 0) {
            // here we will remove the current api_token that has expired from the list of token
            $tokens_list = $user->tokens;
            $tokens_list = array_values(array_diff_key($tokens_list, array_flip($list_to_remove)));
            // unset($tokens_list[$index]);
            $id = $user->id;
            User::where('id', $id)->update(['tokens' => $tokens_list]);
            // $user->tokens = $tokens_list;
            // $user->save();
        }

        // If we have no token we will remove the user
        if (count($user->tokens) == 0) {
            User::findOrFail($user->id)->delete();
            return null;
        }

        return $user;
    }

    // https://code.tutsplus.com/fr/tutorials/how-to-create-a-custom-authentication-guard-in-laravel--cms-29667
    // https://dariopalladino.com/blog/coding/sso-authentication-with-laravel-5-3
    // https://code.tutsplus.com/fr/tutorials/how-to-create-a-custom-authentication-guard-in-laravel--cms-29667
    // https://medium.com/@sirajul.anik/api-gateway-authenticating-user-without-db-storage-in-laravel-lumen-3ef1c1f300d3
    // https://stackoverflow.com/questions/45024429/how-to-add-a-custom-user-provider-in-laravel-5-4
    // https://www.cloudways.com/blog/lumen-rest-api-authentication/
    public function checkByToken($api_token)
    {
        $user = $this->checkTokenInDB($api_token);

        if ($user) {
            return $user;
        }
        // if no users find by token (maybe the user exist but we can not find it by it's token)
        if (!$user) {

            // we ask the Auth service worker if a user with this token exist
            $tokenManager = new TokenManager($api_token);
            $response = $tokenManager->checkToken();

            // Check if there is an error
            if ($response->getStatusCode() !== 200) {
                return $response->getReasonPhrase();
            }

            // Get the content of the response
            $body_resp = json_decode($response->getBody()->getContents())->data;
            if (isset($body_resp->Error)) {
                return $body_resp;
            }
            // $this->setResponseUser($body_resp);
            // dd($this->createUser(new User(), $body_resp));
            return $this->user = $this->createUser(new User(), $body_resp);
            throw new Throwable('Method not implemented.');
        }
        return "Unauthorized";
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

    // protected function setUser($model)
    // {
    //     $user = User::create($model->toArray());
    //     return $user;
    // }

    protected function createUser($model, $body_resp)
    {
        // TODO check in db and
        $user = User::find($body_resp->user->id);
        // dd($body_resp->user->id, $user);
        if (!$user) {
            $time = Carbon::now(); // Date time now
            $time->add(new DateInterval('PT' . $body_resp->token_info->expires_in . 'S')); // We add the duration that left to our token
            $stamp = $time->format('Y-m-d H:i'); // Format conversion

            // we create a new user
            $model->login = $body_resp->user->login;
            $model->first_name = $body_resp->user->first_name;
            $model->last_name = $body_resp->user->last_name;
            $model->email = $body_resp->user->email;
            $model->picture = $body_resp->user->picture;
            $model->active = $body_resp->user->active;
            $model->tokens = [['token' => $body_resp->token_info->token, 'expires_at' => $stamp, 'token_type' => $body_resp->token_info->token_type]];
            $model->save();
            $model->id = $body_resp->user->id;
            $model->save();
            // $this->setRoles($model, $body_resp);
            return $model;
        } else {
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

            $tokens_list = $user->tokens;
            array_push($tokens_list, ['token' => $body_resp->token_info->token, 'expires_at' => $stamp, 'token_type' => $body_resp->token_info->token_type]);
            $user->tokens = $tokens_list;
            $user->save();

            // we check again if all tokens are valid
            $userChecked = $this->checkTokenInDB($body_resp->token_info->token);
            return !$userChecked ? "Unauthorized" : $userChecked;
        }
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
