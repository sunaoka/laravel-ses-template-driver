<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver;

use Aws\Ses\SesClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class Helper
{
    /**
     * Create new Transport
     */
    public function createTransport(): AbstractTransport
    {
        return new SesTemplateTransport(
            $this->createClient(),
            Config::get('services.ses.options', [])  // @phpstan-ignore-line
        );
    }

    /**
     * Create new SES Client
     */
    public function createClient(): SesClient
    {
        $config = array_merge(
            Config::get('services.ses', []),  // @phpstan-ignore-line
            [
                'version' => 'latest',
                'service' => 'email',
            ]
        );

        $config = $this->addSesCredentials($config);

        return new SesClient($config);
    }

    /**
     * Add the SES credentials to the configuration array.
     */
    protected function addSesCredentials(array $config): array
    {
        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return Arr::except($config, ['token']);
    }
}
