# TikTok Business API for [Socialite Providers](https://github.com/SocialiteProviders)

```bash
composer require viktorruskai/tiktok-business-api
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'tiktok' => [
  'client_id' => env('TIKTOK_CLIENT_ID'),
  'client_secret' => env('TIKTOK_CLIENT_SECRET'),
  'redirect' => env('TIKTOK_REDIRECT_URI')
],
```

### Add provider event listener

Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\TikTok\TikTokExtendSocialite::class.'@handle',
    ],
];
```

### Usage

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('tiktok')->redirect();
```

# Returned User Fields

- id
- nickname
- name
- avatar

# Reference

- [TikTok Login Kit](https://developers.tiktok.com/doc/login-kit-web)
