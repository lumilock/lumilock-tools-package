<?php

namespace lumilock\lumilockToolsPackage\App\Services\Contracts;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

class TokenManager
{
    /**
     * The token.
     */
    protected $token;

    /**
     * The uri call to check the token.
     */
    protected $checkUrl;

    /**
     * Create a new service instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(string $token)
    {
        if (!env('SSO_CHECK_URL') || env('SSO_CHECK_URL') === "") {
            Log::error('Please add the variable SSO_CHECK_URL in your .env: ');
            return;
        }
        $this->token = $token;
        $this->checkUrl = env('SSO_CHECK_URL');
    }

    public function checkToken()
    {

        if (isset($this->token)) {
            $headers['Authorization'] = $this->token;
            $headers['Authorization_sso_secret'] = env('SSO_SECRET');
        }
        $client = new Client([
            'base_uri' =>  $this->checkUrl,
            'http_errors' => false,
        ]);

        $promise1 = $client->getAsync('check', [
            'headers'     => $headers,
            'synchronous' => false,
            'timeout' => 5
        ])->then(
            function ($response) {
                return $response;
            }
        );
        return $promise1->wait();
    }
}
