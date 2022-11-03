<?php

namespace SocialiteProviders\TikTok;

use SocialiteProviders\Manager\SocialiteWasCalled;

class TikTokExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('tiktok', Provider::class);
    }
}
