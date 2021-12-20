# Amazon SES template mail driver for Laravel

[![Latest](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/v)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![License](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/license)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![PHP](https://img.shields.io/packagist/php-v/sunaoka/laravel-ses-template-driver/v2.x-dev)](composer.json)
[![Laravel](https://img.shields.io/badge/laravel-7.x%20%7C%7C%208.x-red)](https://laravel.com/)
[![Test](https://github.com/sunaoka/laravel-ses-template-driver/actions/workflows/test.yml/badge.svg?branch=v2.x)](https://github.com/sunaoka/laravel-ses-template-driver/actions/workflows/test.yml)
[![codecov](https://codecov.io/gh/sunaoka/laravel-ses-template-driver/branch/v2.x/graph/badge.svg)](https://codecov.io/gh/sunaoka/laravel-ses-template-driver)

----

A Mail Driver with support for [Using templates to send personalized emails with the Amazon SES API](https://docs.aws.amazon.com/ses/latest/dg/send-personalized-email-api.html).

## Version Compatibility

| Laravel | Amazon SES template mail driver |
| ------- | ------------------------------- |
| 5.7.x   | 1.x                             |
| 5.8.x   | 1.x                             |
| 6.x     | 1.x                             |
| 7.x     | 2.x                             |
| 8.x     | 2.x                             |
| 9.x     | 3.x                             |

## Installation

### Laravel 5.7.x, 5.8.x, 6.x

```bash
composer require sunaoka/laravel-ses-template-driver:'^1.0'
```

### Laravel 7.x, 8.x

```bash
composer require sunaoka/laravel-ses-template-driver:'^2.0'
```

### Laravel 9.x

```bash
composer require sunaoka/laravel-ses-template-driver
```

Next, set the following in `config/mail.php` and `config/services.php`.

### config/mail.php

#### Laravel 5.7.x, 5.8.x, 6.x

```php
'driver' => 'ses.template',
```

#### Laravel 7.x, 8.x, 9.x

```php
'default' => 'sestemplate',

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

## Artisan Console Commands

### Lists the email templates present in your Amazon SES account in the current AWS Region.

#### Options

```bash
php artisan ses-template:list-templates --help
```

```text
Description:
  Lists the email templates present in your Amazon SES account in the current AWS Region

Usage:
  ses-template:list-templates [options]

Options:
      --name            Sort by the name of the template [default]
      --time            Sort by the time and date the template was created
      --asc             Sort by ascending order [default]
      --desc            Sort by descending order
      --json            The output is formatted as a JSON string
```

#### Output Text format

```bash
php artisan ses-template:list-templates
```

```text
+----+-------------+---------------------------+
| No | Name        | Created At                |
+----+-------------+---------------------------+
| 0  | MyTemplate  | 2020-11-24T15:01:21+00:00 |
| 1  | MyTemplate2 | 2020-11-24T15:01:25+00:00 |
+----+-------------+---------------------------+

Enter a number to display the template object:
> 0

TemplateName:
MyTemplate

SubjectPart:
Greetings, {{name}}!

TextPart:
Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.

HtmlPart:
<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>
```

#### Output JSON format

```bash
php artisan ses-template:list-templates --json
```

```json
{
  "TemplatesMetadata": [
    {
      "Name": "MyTemplate",
      "CreatedTimestamp": "2020-11-24T15:01:21+00:00"
    },
    {
      "Name": "MyTemplate2",
      "CreatedTimestamp": "2020-11-24T15:01:25+00:00"
    }
  ]
}
```

### Displays the template object for the template you specify

#### Options

```bash
php artisan ses-template:get-template --help
```

```text
Description:
  Displays the template object for the template you specify

Usage:
  ses-template:get-template [options] [--] <TemplateName>

Arguments:
  TemplateName          The name of the template to retrieve

Options:
      --json            The output is formatted as a JSON string
```

#### Output Text format

```bash
php artisan ses-template:get-template MyTemplate
```

```text
TemplateName:
MyTemplate

SubjectPart:
Greetings, {{name}}!

TextPart:
Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.

HtmlPart:
<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>
```

#### Output JSON format

```bash
php artisan ses-template:get-template MyTemplate --json
```

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

## Reference

- [Mail - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/master/mail)
- [Using templates to send personalized emails with the Amazon SES API](https://docs.aws.amazon.com/ses/latest/dg/send-personalized-email-api.html)
