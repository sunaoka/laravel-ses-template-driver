<?php

namespace Sunaoka\LaravelSesTemplateDriver;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\ServiceProvider;

class SesTemplateTransportServiceProvider extends ServiceProvider
{
    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    public function register(): void
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
    public function registerTransport(TransportManager $manager): void
    {
        $manager->extend('ses.template', function () {
            return (new Helper($this->app))->createTransport();
        });
    }
}
