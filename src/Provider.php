<?php

namespace RedSnapper\SocialiteProviders\DocCheck;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\User;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'DOCCHECK';

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://login.doccheck.com/code/',
            $state
        );
    }

    protected function getCodeFields($state = null)
    {
        $fields = [
            'dc_client_id' => $this->clientId,
            'dc_template'  => 'fullscreen_dc',
            'redirect_uri' => $this->redirectUrl,
            'state'        => $state,
        ];

        return array_merge($fields, $this->parameters);
    }

    protected function getTokenUrl()
    {
        return "https://login.doccheck.com/service/oauth/access_token/";
    }

    protected function getUserByToken($token)
    {
        // User has revoked the token so we can not retrieve user data
        if ($this->request->input('dc_agreement') == 0) {
            return ['uniquekey' => $this->request->input('uniquekey')];
        }

        $response = $this->getHttpClient()->get('https://login.doccheck.com/service/oauth/user_data/', [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): DocCheckUser
    {
        return (new DocCheckUser())->setRaw($user)->map([
            'id'    => $user['uniquekey'],
            'name'  => Arr::get($user, 'address_name_first')." ".Arr::get($user, 'address_name_last'),
            'email' => Arr::get($user, 'email'),
        ]);
    }

}