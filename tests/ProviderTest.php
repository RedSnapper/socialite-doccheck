<?php

namespace RedSnapper\SocialiteProviders\DocCheck\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use RedSnapper\SocialiteProviders\DocCheck\DocCheckUser;
use RedSnapper\SocialiteProviders\DocCheck\Provider;

class ProviderTest extends TestCase
{
    #[Test]
    public function it_builds_the_authorize_url_with_lang_path_segment(): void
    {
        $provider = $this->makeProvider();

        $url = $provider->with(['lang' => 'de'])->redirect()->getTargetUrl();

        $this->assertStringStartsWith('https://auth.doccheck.com/de/authorize?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('client_id', $query['client_id']);
        $this->assertSame('http://localhost/callback', $query['redirect_uri']);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame('unique_id', $query['scope']);
        $this->assertNotEmpty($query['state']);
        $this->assertArrayNotHasKey('lang', $query, 'lang must be a path segment, not a query param');
    }

    #[Test]
    public function it_defaults_lang_to_en_when_not_provided(): void
    {
        $provider = $this->makeProvider();

        $url = $provider->redirect()->getTargetUrl();

        $this->assertStringStartsWith('https://auth.doccheck.com/en/authorize?', $url);
    }

    #[Test]
    public function scopes_method_merges_with_default_unique_id(): void
    {
        $provider = $this->makeProvider();

        $url = $provider->scopes(['name', 'email', 'occupation_detail'])->redirect()->getTargetUrl();

        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('unique_id name email occupation_detail', $query['scope']);
    }

    #[Test]
    public function it_can_retrieve_a_fully_populated_user(): void
    {
        $provider = $this->makeProvider(['state' => 'matching-state'], sessionState: 'matching-state');
        $provider->setHttpClient($this->mockGuzzleForUser([
            'unique_id' => 'c2533d51-4dfe-4531-bc83-9bf62e1915e3',
            'first_name' => 'Micha',
            'last_name' => 'Muster',
            'email' => 'test@example.com',
            'discipline_id' => 387,
            'profession_id' => 100032,
        ]));

        $user = $provider->user();

        $this->assertInstanceOf(DocCheckUser::class, $user);
        $this->assertSame('c2533d51-4dfe-4531-bc83-9bf62e1915e3', $user->getId());
        $this->assertSame('Micha Muster', $user->getName());
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame(387, $user->getOccupationDisciplineId());
        $this->assertSame(100032, $user->getOccupationProfessionId());
    }

    #[Test]
    public function it_handles_anonymous_payload_with_only_unique_id(): void
    {
        $provider = $this->makeProvider(['state' => 'matching-state'], sessionState: 'matching-state');
        $provider->setHttpClient($this->mockGuzzleForUser([
            'unique_id' => '841f41d1-59b5-45fe-b716-8e45f9d58a40',
        ]));

        $user = $provider->user();

        $this->assertSame('841f41d1-59b5-45fe-b716-8e45f9d58a40', $user->getId());
        $this->assertNull($user->getName());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getOccupationDisciplineId());
        $this->assertNull($user->getOccupationProfessionId());
    }

    #[Test]
    public function it_handles_partial_payload_when_optional_scopes_declined(): void
    {
        $provider = $this->makeProvider(['state' => 'matching-state'], sessionState: 'matching-state');
        $provider->setHttpClient($this->mockGuzzleForUser([
            'unique_id' => 'partial-uuid',
            'email' => 'partial@example.com',
        ]));

        $user = $provider->user();

        $this->assertSame('partial-uuid', $user->getId());
        $this->assertSame('partial@example.com', $user->getEmail());
        $this->assertNull($user->getName());
        $this->assertNull($user->getOccupationDisciplineId());
        $this->assertNull($user->getOccupationProfessionId());
    }

    #[Test]
    public function it_returns_a_single_name_when_only_first_name_is_present(): void
    {
        $provider = $this->makeProvider(['state' => 'matching-state'], sessionState: 'matching-state');
        $provider->setHttpClient($this->mockGuzzleForUser([
            'unique_id' => 'first-only-uuid',
            'first_name' => 'Micha',
        ]));

        $user = $provider->user();

        $this->assertSame('Micha', $user->getName());
    }

    private function makeProvider(array $requestParams = [], ?string $sessionState = null): Provider
    {
        $request = new Request($requestParams);
        $session = $this->app->make('session')->driver('array');
        if ($sessionState !== null) {
            $session->put('state', $sessionState);
        }
        $request->setLaravelSession($session);

        return new Provider($request, 'client_id', 'client_secret', 'http://localhost/callback');
    }

    private function mockGuzzleForUser(array $userData): Client
    {
        $accessTokenResponse = $this->mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns(
            Utils::streamFor(json_encode(['access_token' => 'fake-token']))
        );

        $userResponse = $this->mock(ResponseInterface::class);
        $userResponse->allows('getBody')->andReturns(Utils::streamFor(json_encode($userData)));

        $guzzle = $this->mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);
        $guzzle->expects('get')->andReturns($userResponse);

        return $guzzle;
    }
}
