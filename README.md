# Amazon SES template mail driver for Laravel

[![Latest](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/v)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![License](https://poser.pugx.org/sunaoka/laravel-ses-template-driver/license)](https://packagist.org/packages/sunaoka/laravel-ses-template-driver)
[![PHP](https://img.shields.io/packagist/php-v/sunaoka/laravel-ses-template-driver)](composer.json)
[![Laravel](https://img.shields.io/badge/laravel-10.x%20%7C%2011.x%20%7C%2012.x-red)](https://laravel.com/)
[![Test](https://github.com/sunaoka/laravel-ses-template-driver/actions/workflows/test.yml/badge.svg?branch=develop)](https://github.com/sunaoka/laravel-ses-template-driver/actions/workflows/test.yml)
[![codecov](https://codecov.io/gh/sunaoka/laravel-ses-template-driver/branch/develop/graph/badge.svg)](https://codecov.io/gh/sunaoka/laravel-ses-template-driver)

----

A Mail Driver with support for [Using templates to send personalized emails with the Amazon SES API](https://docs.aws.amazon.com/ses/latest/dg/send-personalized-email-api.html).

## Support Policy

| Version (*1) | Laravel (*2) | PHP (*3)  |
| ------------ | ------------ |-----------|
| [1][v1.x]    | 5.7 - 6      | 7.1 - 7.4 |
| [2][v2.x]    | 7 - 8        | 7.2 - 8.1 |
| [3][v3.x]    | 9 - 11       | 8.0 - 8.4 |
| 4            | 10 - 12      | 8.1 - 8.4 |

(*1) Supported Amazon SES template mail driver (This Driver) version

(*2) Supported Laravel versions

(*3) Supported PHP versions

## Installation

```bash
composer require sunaoka/laravel-ses-template-driver
```

Next, set the following in `config/mail.php` and `config/services.php`.

### config/mail.php

```php
'default' => 'sestemplate',

'mailers' => [
    'sestemplate' => [
        'transport' => 'sestemplate',  // or `sesv2template` - When using Amazon SES API v2
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
        'TenantName'           => 'MyTenant',  // using Amazon SES API v2 with AWS SDK for PHP 3.352.0 or later
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
use Illuminate\Support\Facades\Mail;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;

class Foo
{
    public function sendmail()
    {
        $templateName = 'MyTemplate';
        $templateData = [
            'name' => 'Alejandro',
            'favoriteanimal' => 'alligator',
        ];

        $result = Mail::to('alejandro.rosalez@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->send(new SesTemplate($templateName, $templateData));
            
        echo $result->getMessageId();  // Message-ID overwritten by Amazon SES
    }
}
```

### Options

Set `From`, `Reply-To` and custom header.

```php
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplateOptions;

class Foo
{
    public function sendmail()
    {
        $templateName = 'MyTemplate';
        $templateData = [
            'name' => 'Alejandro',
            'favoriteanimal' => 'alligator',
        ];

        $options = new SesTemplateOptions();
        $options->from(new Address('alejandro.rosalez@example.com', 'Alejandro Rosalez'))
                ->replyTo(new Address('alejandro.rosalez@example.com'));

        // Only with Amazon SES API v2 ('transport' is `sesv2template`)
        $options->header('X-Custom-Header1', 'Custom Value 1')
                ->header('X-Custom-Header2', 'Custom Value 2');

        // You can also set it in the constructor.
        $options = new SesTemplateOptions(
            from: new Address('alejandro.rosalez@example.com', 'Alejandro Rosalez'),
            replyTo: new Address('alejandro.rosalez@example.com'),
            headers: [
                'X-Custom-Header1' => 'Custom Value 1',
                'X-Custom-Header2' => 'Custom Value 2',
            ],
        );

        $result = Mail::to('alejandro.rosalez@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->send(new SesTemplate($templateName, $templateData, $options));
            
        echo $result->getMessageId();  // Message-ID overwritten by Amazon SES
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

Amazon SES API v2

```json
{
  "TemplatesMetadata": [
    {
      "TemplateName": "MyTemplate",
      "CreatedTimestamp": "2020-11-24T15:01:21+00:00"
    },
    {
      "TemplateName": "MyTemplate2",
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

Amazon SES API v2

```json
{
  "Template": {
    "TemplateName": "MyTemplate",
    "TemplateContent": {
      "Subject": "Greetings, {{name}}!",
      "Html": "<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>",
      "Text": "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}."
    }
  }
}
```

## AWS Identity and Access Management (IAM) Policy

### Amazon SES API (v1)

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ses:SendTemplatedEmail",
        "ses:ListTemplates",
        "ses:GetTemplate"
      ],
      "Resource": "*"
    }
  ]
}
```

### Amazon SES API v2

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ses:SendEmail",
        "ses:ListEmailTemplates",
        "ses:GetEmailTemplate"
      ],
      "Resource": "*"
    }
  ]
}
```

## Reference

- [Mail - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/master/mail)
- [Using templates to send personalized emails with the Amazon SES API](https://docs.aws.amazon.com/ses/latest/dg/send-personalized-email-api.html)
- [Class Aws\Ses\SesClient | AWS SDK for PHP 3.x](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Ses.SesClient.html)
- [Class Aws\SesV2\SesV2Client | AWS SDK for PHP 3.x](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.SesV2.SesV2Client.html)

[v1.x]: https://github.com//sunaoka/laravel-ses-template-driver/tree/v1.x
[v2.x]: https://github.com//sunaoka/laravel-ses-template-driver/tree/v2.x
[v3.x]: https://github.com//sunaoka/laravel-ses-template-driver/tree/v3.x
