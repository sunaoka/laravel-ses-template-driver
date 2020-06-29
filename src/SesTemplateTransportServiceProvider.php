<?php

namespace Sunaoka\LaravelSesTemplateDriver;

use Aws\Ses\SesClient;
use Illuminate\Mail\MailManager;
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
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            return $this->registerTransport($manager);
        });
    }

    /**
     * Register Transport
     *
     * @param MailManager $manager
     */
    public function registerTransport(MailManager $manager)
    {
        $manager->extend('sestemplate', function () {
            $config = array_merge($this->app['config']->get('services.ses', []), [
                'version' => 'latest', 'service' => 'email',
            ]);

            return new SesTemplateTransport(
                new SesClient($this->addSesCredentials($config)),
                $config['options'] ?? []
            );
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
        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }
}
