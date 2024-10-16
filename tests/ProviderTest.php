<?php

namespace RedSnapper\SocialiteProviders\DocCheck\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use Psr\Http\Message\ResponseInterface;
use RedSnapper\SocialiteProviders\DocCheck\DocCheckUser;
use RedSnapper\SocialiteProviders\DocCheck\Provider;

class ProviderTest extends TestCase
{
    /** @test */
    public function it_can_get_a_redirect_response()
    {
        $request = new Request();
        $session = $this->app->make('session')->driver('array');
        $request->setLaravelSession($session);

        $provider = new Provider($request, 'client_id', 'client_secret', 'redirect');

        $redirect = $provider->redirect()->getTargetUrl();

        $this->assertStringContainsString("?dc_client_id=client_id", $redirect);
        $this->assertStringContainsString("redirect_uri=redirect", $redirect);
    }

    /** @test */
    public function can_retrieve_a_user()
    {
        $request = new Request(['state' => 'state', 'dc_agreement' => 1]);
        $session = $this->app->make('session')->driver('array');
        $session->put('state', 'state');
        $request->setLaravelSession($session);

        $basicProfileResponse = $this->mock(ResponseInterface::class);
        $basicProfileResponse->allows('getBody')->andReturns(Utils::streamFor(json_encode([
            'uniquekey'                       => '1',
            'email'                           => 'web@redsnapper.net',
            'occupation_discipline_id'        => 33,
            'occupation_profession_id'        => 44,
            'occupation_profession_parent_id' => 55,
        ])));

        $accessTokenResponse = $this->mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns(
            Utils::streamFor(json_encode(['access_token' => 'fake-token']))
        );

        $guzzle = $this->mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);

        $guzzle->expects('get')->andReturns($basicProfileResponse);

        $provider = new Provider($request, 'client_id', 'client_secret', 'redirect');

        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(DocCheckUser::class, $user);
        $this->assertEquals(1, $user->getId());
        $this->assertEquals("web@redsnapper.net", $user->getEmail());
        $this->assertEquals(33, $user->getOccupationDisciplineId());
        $this->assertEquals(44, $user->getOccupationProfessionId());
        $this->assertEquals(55, $user->getOccupationProfessionParentId());
    }

    /** @test */
    public function can_retrieve_basic_user_when_agreement_not_given()
    {
        $request = new Request(['state' => 'state', 'dc_agreement' => 0, 'uniquekey' => 1]);
        $session = $this->app->make('session')->driver('array');
        $session->put('state', 'state');
        $request->setLaravelSession($session);


        $accessTokenResponse = $this->mock(ResponseInterface::class);
        $accessTokenResponse->allows('getBody')->andReturns(
            Utils::streamFor(json_encode(['access_token' => 'fake-token']))
        );

        $guzzle = $this->mock(Client::class);
        $guzzle->expects('post')->andReturns($accessTokenResponse);

        $provider = new Provider($request, 'client_id', 'client_secret', 'redirect');

        $provider->setHttpClient($guzzle);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->getId());
        $this->assertNull($user->getEmail());
    }
}