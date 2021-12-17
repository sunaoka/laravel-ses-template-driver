<?php

namespace Sunaoka\LaravelSesTemplateDriver;

use Illuminate\Mail\MailManager;
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
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $this->registerTransport($manager);
        });
    }

    /**
     * Register Transport
     *
     * @param MailManager $manager
     */
    public function registerTransport(MailManager $manager): void
    {
        $manager->extend('sestemplate', function () {
            return (new Helper($this->app))->createTransport();
        });
    }
}
