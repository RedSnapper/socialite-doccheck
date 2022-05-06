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

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('doccheck')->redirect();
```

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
