<?php

namespace RedSnapper\SocialiteProviders\DocCheck;

use SocialiteProviders\Manager\SocialiteWasCalled;

class DocCheckExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('doccheck', Provider::class);
    }
}