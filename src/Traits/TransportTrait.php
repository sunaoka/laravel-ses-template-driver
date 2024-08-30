<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Traits;

use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesV2TemplateTransport;

trait TransportTrait
{
    use SesClientTrait;

    /**
     * Create an instance of the Symfony Amazon SES Transport driver.
     */
    public function createSesTemplateTransport(array $config = []): SesTemplateTransport
    {
        return new SesTemplateTransport(
            $this->createSesClient($config),
            config('services.ses.options', [])  // @phpstan-ignore argument.type
        );
    }

    /**
     * Create an instance of the Symfony Amazon SES V2 Transport driver.
     */
    public function createSesV2TemplateTransport(array $config = []): SesV2TemplateTransport
    {
        return new SesV2TemplateTransport(
            $this->createSesV2Client($config),
            config('services.ses.options', [])  // @phpstan-ignore argument.type
        );
    }
}
