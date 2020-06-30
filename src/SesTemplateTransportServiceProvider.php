<?php

namespace Sunaoka\LaravelSesTemplateDriver;

use Aws\Ses\SesClient;
use Illuminate\Mail\TransportManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;

class SesTemplateTransportServiceProvider extends ServiceProvider
{
    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(TransportManager::class, function (TransportManager $manager) {
            $this->registerTransport($manager);
        });
    }

    /**
     * Register Transport
     *
     * @param TransportManager $manager
     */
    public function registerTransport(TransportManager $manager)
    {
        $manager->extend('ses.template', function () {
            $config = array_merge($this->app['config']->get('services.ses', []), [
                'version' => 'latest',
                'service' => 'email',
            ]);
            $client = new SesClient($this->addSesCredentials($config));

            return new SesTemplateTransport($client, $config['options'] ?? []);
        });
    }

    /**
     * Add the SES credentials to the configuration array.
     *
     * @param  array $config
     *
     * @return array
     */
    protected function addSesCredentials(array $config)
    {
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }
}
