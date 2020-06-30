# Amazon SES template mail driver for Laravel 5, 6 and 7

[![Latest](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/v)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![License](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/license)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![PHP](https://img.shields.io/packagist/php-v/sunaoka/laravel-ses-template-driver/v1.0.0)](composer.json)
[![Laravel](https://img.shields.io/badge/laravel-5.x%20%7C%206.x-red)](https://laravel.com/)
[![Build](https://travis-ci.org/sunaoka/laravel-ses-template-driver.svg?branch=v1.x)](https://travis-ci.org/sunaoka/laravel-ses-template-driver)
[![codecov](https://codecov.io/gh/sunaoka/laravel-ses-template-driver/branch/v1.x/graph/badge.svg)](https://codecov.io/gh/sunaoka/laravel-ses-template-driver)

----

A Mail Driver with support for [Sending Personalized Email Using the Amazon SES API](https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-personalized-email-api.html).

## Version Compatibility

| Laravel | Amazon SES template mail driver |
| ------- | ------------------------------- |
| 5.7.x   | 1.x                             |
| 5.8.x   | 1.x                             |
| 6.x     | 1.x                             |
| 7.x     | 2.x                             |

## Installation

### Laravel 5.7.x, 5.8.x, 6.x

```bash
composer require sunaoka/laravel-ses-template-driver:'^1.0'
```

### Laravel 7.x

```bash
composer require sunaoka/laravel-ses-template-driver
```

Next, set the following in `config/mail.php` and `config/services.php`.

### config/mail.php

#### Laravel 5.7.x, 5.8.x, 6.x

```php
'driver' => 'ses.template',
```

#### Laravel 7.x

```php
'driver' => 'sestemplate',

'mailers' => [
    'sestemplate' => [
        'transport' => 'sestemplate',
    ],
],
```

### config/services.php

```php
'ses' => [
    'key'    => 'your-ses-key',
    'secret' => 'your-ses-secret',
    'region' => 'ses-region',  // e.g. us-east-1
],
```

If you need to include [additional options](https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-email-2010-12-01.html#sendtemplatedemail) when executing the SES `SendTemplatedEmail` request, you may define an `options` array within your `ses` configuration:

```php
'ses' => [
    'key'     => 'your-ses-key',
    'secret'  => 'your-ses-secret',
    'region'  => 'ses-region',  // e.g. us-east-1
    'options' => [
        'ConfigurationSetName' => 'MyConfigurationSet',
        'Tags' => [
            [
                'Name'  => 'foo',
                'Value' => 'bar',
            ],
        ],
    ],
],
```

## Basic usage

```php
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;

class Foo
{
    public function sendmail()
    {
        $templateName = 'MyTemplate';
        $templateData = [
            'name'           => 'Alejandro',
            'favoriteanimal' => 'alligator',
        ];

        \Mail::to('alejandro.rosalez@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->send(new SesTemplate($templateName, $templateData));
    }
}
```

### Options

Set  Reply-to header

```php
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;

class Foo
{
    public function sendmail()
    {
        $templateName = 'MyTemplate';
        $templateData = [
            'name'           => 'Alejandro',
            'favoriteanimal' => 'alligator',
        ];
        $options = [
            'from' => [
                'address' => 'alejandro.rosalez@example.com', // required
                'name'    => 'Alejandro Rosalez',             // optional
            ],
            'reply_to' => [
                'address' => 'alejandro.rosalez@example.com', // required
                'name'    => 'Alejandro Rosalez',             // optional
            ],
        ];

        \Mail::to('alejandro.rosalez@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->send(new SesTemplate($templateName, $templateData, $options));
    }
}
```

### To send a templated email to a single destination

```json
{
  "Template": {
    "TemplateName": "MyTemplate",
    "SubjectPart": "Greetings, {{name}}!",
    "HtmlPart": "<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>",
    "TextPart": "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}."
  }
}
```

> Not supported, to send a templated email to multiple destinations.

## Reference

- [Mail - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/master/mail)
- [Sending Personalized Email Using the Amazon SES API](https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-personalized-email-api.html)
