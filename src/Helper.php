<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver;

use Aws\Ses\SesClient;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;

class Helper
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Create new Transport
     *
     * @return Transport
     */
    public function createTransport(): Transport
    {
        return new SesTemplateTransport(
            $this->createClient(),
            $this->app['config']->get('services.ses.options', [])
        );
    }

    /**
     * Create new SES Client
     *
     * @return SesClient
     */
    public function createClient(): SesClient
    {
        $config = array_merge($this->app['config']->get('services.ses', []), [
            'version' => 'latest',
            'service' => 'email',
        ]);

        $config = $this->addSesCredentials($config);

        return new SesClient($config);
    }

    /**
     * Add the SES credentials to the configuration array.
     *
     * @param array $config
     *
     * @return array
     */
    protected function addSesCredentials(array $config): array
    {
        if (!empty($config['key']) && !empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }
}
