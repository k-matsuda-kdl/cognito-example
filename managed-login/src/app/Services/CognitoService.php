<?php

namespace App\Services;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Session;

class CognitoService
{
    public function getLoginUrl()
    {
        $clientId = env('COGNITO_CLIENT_ID');
        $redirectUri = env('COGNITO_REDIRECT_URI');
        $domain = env('COGNITO_DOMAIN');
        return 'https://' . $domain . '/login?client_id=' . $clientId . '&response_type=code&scope=aws.cognito.signin.user.admin+email+openid+phone&lang=ja&redirect_uri=' . urlencode($redirectUri);
    }

    public function getLogoutUrl()
    {
        $clientId = env('COGNITO_CLIENT_ID');
        $redirectUri = env('COGNITO_LOGOUT_REDIRECT_URI');
        $domain = env('COGNITO_DOMAIN');
        return 'https://' . $domain . '/logout?client_id=' . $clientId . '&lang=ja&logout_uri=' . $redirectUri;
    }

    public function getPassKeyUrl()
    {
        $clientId = env('COGNITO_CLIENT_ID');
        $redirectUri = env('COGNITO_REDIRECT_URI');
        $domain = env('COGNITO_DOMAIN');
        return 'https://' . $domain . '/passkeys/add?client_id=' . $clientId . '&lang=ja&redirect_uri=' . $redirectUri;
    }

    public function getTokenEndpoint()
    {
        $domain = env('COGNITO_DOMAIN');
        return 'https://' . $domain . '/oauth2/token';
    }
}