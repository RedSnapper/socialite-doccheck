# DocCheck Authentication
With the DocCheck Login you are able to very simply establish a protected area on your site, which is only
accessible to (medical) professionals The DocCheck password protection is an identification service for
medical sites which is compliant with the German law for pharmaceutical commercial information HWG
(Heilmittelwerbegesetz)


[![Latest Version on Packagist](https://img.shields.io/packagist/v/rs/socialite-doccheck.svg?style=flat-square)](https://packagist.org/packages/rs/socialite-doccheck)
[![GitHub Tests Action Status](https://github.com/redsnapper/socialite-doccheck/workflows/run-tests/badge.svg)](https://github.com/redsnapper/socialite-doccheck/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/rs/socialite-doccheck.svg?style=flat-square)](https://packagist.org/packages/rs/socialite-doccheck)

## Installation

You can install the package via composer:

```bash
composer require rs/socialite-doccheck
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'doccheck' => [    
  'client_id' => env('DOCCHECK_CLIENT_ID'),  
  'client_secret' => env('DOCCHECK_CLIENT_SECRET'),  
  'redirect' => env('DOCCHECK_REDIRECT_URI') 
],
```

### Add provider event listener

Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \RedSnapper\SocialiteProviders\DocCheck\DocCheckExtendSocialite::class
    ],
];
```

### Usage

DocCheck's OAuth 2.0 flow requires a language path segment in the authorize URL. Pass it via Socialite's `with()`:

```php
return Socialite::driver('doccheck')
    ->scopes(['name', 'email', 'occupation_detail'])
    ->with(['lang' => 'de'])
    ->redirect();
```

The package always requests the `unique_id` scope (DocCheck's mandatory system scope). Add any other scopes your application needs via `->scopes([...])`. Available scopes include `name`, `email`, `profession`, `occupation_detail`, `country`, `language`, and `address` — see DocCheck's [scopes documentation](https://docs.doccheck.com/login-access/features/personal.html) for the full list and license requirements. Requested scopes are presented to the user as optional ticks on the consent screen by default; if you need a scope to be **required** rather than optional, add it to the login-client's "Mandatory Scopes" in DocCheck Access.

After authentication, the user payload uses DocCheck's v2 keys (`unique_id`, `first_name`, `last_name`, `email`, `discipline_id`, `profession_id`). Declined optional scopes are simply omitted from the response — the package's accessors return `null` cleanly in that case.

### Handling authorization errors

DocCheck can redirect back to your callback URL with an OAuth 2.0 error response (`?error=…&error_description=…`) — for example `R0100_PROFESSION_NOT_ALLOWED` when the user's profession isn't on the login-client's whitelist. The package surfaces this as a typed `DocCheckAuthorizationException` from `->user()`:

```php
use RedSnapper\SocialiteProviders\DocCheck\DocCheckAuthorizationException;

try {
    $user = Socialite::driver('doccheck')->user();
} catch (DocCheckAuthorizationException $e) {
    // $e->error            e.g. 'R0100_PROFESSION_NOT_ALLOWED' or 'access_denied'
    // $e->errorDescription human-readable text from DocCheck, may be null
    return redirect()->route('login')->withErrors([
        'doccheck' => __('auth.doccheck.not_eligible'),
    ]);
}
```

DocCheck shows its own branded error page before redirecting, so the user already knows roughly what happened — your handler just needs to land them somewhere coherent. The package deliberately does not enumerate DocCheck's error codes; branch on `$e->error` in your application as needed.

### Upgrading from v1

v2 is a breaking change. v1 endpoints (`login.doccheck.com`) are being decommissioned by DocCheck on 2026-06-01. If your application is not yet ready to migrate, lock to `^1.0` — the `v1.x` tags remain available.

For full breaking-change details, see [CHANGELOG](CHANGELOG.md).

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email param@redsnapper.net instead of using the issue tracker.

## Credits

-   [Param Dhaliwal](https://github.com/rs)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
