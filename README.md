# Amazon SES template mail driver for Laravel 5

[![Latest Stable Version](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/v/stable)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![License](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/license)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![Build Status](https://travis-ci.org/sunaoka/laravel-ses-template-driver.svg?branch=develop)](https://travis-ci.org/sunaoka/laravel-ses-template-driver)
[![codecov](https://codecov.io/gh/sunaoka/laravel-ses-template-driver/branch/develop/graph/badge.svg)](https://codecov.io/gh/sunaoka/laravel-ses-template-driver)

----

A Mail Driver with support for [Sending Personalized Email Using the Amazon SES API](https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-personalized-email-api.html).

## Installation

```bash
composer require sunaoka/laravel-ses-template-driver
```

Next, set the `driver` option in your `config/mail.php` configuration file to `ses.template` and verify that your `config/services.php` configuration file contains the following options:

```php
'driver' => 'ses.template',

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

## usage
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

## Tips

### How to set Reply-to header

Call the `alwaysReplyTo` method before the `send` method.

```php
\Mail::alwaysReplyTo('reply-to@example.com');

\Mail::to('alejandro.rosalez@example.com')
    ->send(new SesTemplate($templateName, $templateData));
```

or, set the `reply_to` option in your `config/mail.php` configuration file.

```php
'reply_to' => [
    'address' => 'reply-to@example.com',
    'name'    => 'Reply to name',
],
```

## Reference

- [Mail - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/master/mail)
- [Sending Personalized Email Using the Amazon SES API](https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-personalized-email-api.html)
