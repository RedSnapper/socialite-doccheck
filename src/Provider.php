<?php

namespace RedSnapper\SocialiteProviders\DocCheck;

use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'DOCCHECK';

    private const BASE_URL = 'https://auth.doccheck.com';

    protected $scopes = ['unique_id'];

    protected $scopeSeparator = ' ';

    public function user()
    {
        if ($this->request->filled('error')) {
            throw new DocCheckAuthorizationException(
                error: (string) $this->request->input('error'),
                errorDescription: $this->request->input('error_description'),
            );
        }

        return parent::user();
    }

    protected function getAuthUrl($state): string
    {
        $lang = Arr::pull($this->parameters, 'lang', 'en');

        return $this->buildAuthUrlFromBase(
            self::BASE_URL.'/'.$lang.'/authorize',
            $state
        );
    }

    protected function getTokenUrl(): string
    {
        return self::BASE_URL.'/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(self::BASE_URL.'/api/users/data', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): DocCheckUser
    {
        $name = trim(Arr::get($user, 'first_name', '').' '.Arr::get($user, 'last_name', ''));

        return (new DocCheckUser())->setRaw($user)->map([
            'id' => Arr::get($user, 'unique_id'),
            'name' => $name !== '' ? $name : null,
            'email' => Arr::get($user, 'email'),
        ]);
    }
}
