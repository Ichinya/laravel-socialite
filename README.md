# Socialite for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ichinya/laravel-socialite.svg?style=flat-square)](https://packagist.org/packages/ichinya/laravel-socialite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ichinya/laravel-socialite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ichinya/laravel-socialite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ichinya/laravel-socialite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ichinya/laravel-socialite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ichinya/laravel-socialite.svg?style=flat-square)](https://packagist.org/packages/ichinya/laravel-socialite)

Вход через социальные сети для Laravel. Используются драйвера Laravel Socialite.

## Установка

Вы можете установить пакет через composer:

```bash
composer require ichinya/laravel-socialite
```

После установки пакета, вы можете опубликовать конфигурацию с помощью команды:

```bash
php artisan vendor:publish --tag=ichinya-socialite
```

## Использование

Устанавливаем нужные драйвера через composer:

```bash
composer require socialiteproviders/github
composer require socialiteproviders/google
```

Добавляем иконки в `config/socialite.php`:

```php
    'drivers' => [
        'google' => '/socialite/google.svg',
        'github' => '/socialite/github.svg',
    ],
```

Добавляем в `config/services.php`:

```php
  'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI')
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI')
    ],

```

Нужно просто добавить на форе входа кнопки входа через социальные сети:

```blade
<a href="{{ route('socialite.redirect', ['driver' => 'github',]) }}" class="btn btn-primary">
    Войти через GitHub
</a>
<a href="{{ route('socialite.redirect', ['driver' => 'google',]) }}" class="btn btn-danger">
    Войти через Google
</a>
```

## MoonShine

Пока что поддерживается только работа связкой Laravel + MoonShine. Но в будущем планируется отвязка от панели.

## Журнал изменений

Пожалуйста, смотрите [CHANGELOG](CHANGELOG.md) для получения информации о недавних изменениях.

## Contributing (Содействие проекту)

Пожалуйста, смотрите [CONTRIBUTING](CONTRIBUTING.md) для подробностей.

## Credits

- [Ichi](https://github.com/ichinya)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
