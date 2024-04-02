<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Traits;

use Aws\Ses\SesClient;
use Aws\SesV2\SesV2Client;
use Illuminate\Support\Arr;

trait SesClientTrait
{
    /**
     * Create an instance of the Amazon SES Client.
     */
    public function createSesClient(array $config = []): SesClient
    {
        $config = array_merge(
            config('services.ses', []),  // @phpstan-ignore-line
            ['version' => 'latest', 'service' => 'email'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesClient($this->addSesCredentials($config));
    }

    /**
     * Create an instance of the Amazon SES V2 Client.
     */
    public function createSesV2Client(array $config = []): SesV2Client
    {
        $config = array_merge(
            config('services.ses', []),  // @phpstan-ignore-line
            ['version' => 'latest'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesV2Client($this->addSesCredentials($config));
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
