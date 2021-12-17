<?php

namespace Sunaoka\LaravelSesTemplateDriver;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\ServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Commands\GetTemplateCommand;
use Sunaoka\LaravelSesTemplateDriver\Commands\ListTemplatesCommand;

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

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
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

    /**
     * Register Commands
     *
     * @return void
     */
    public function registerCommands(): void
    {
        $this->app->singleton('command.ses-template.list-templates', function ($app) {
            return new ListTemplatesCommand((new Helper($app))->createClient());
        });

        $this->app->singleton('command.ses-template.get-template', function ($app) {
            return new GetTemplateCommand((new Helper($app))->createClient());
        });

        $this->commands(
            'command.ses-template.list-templates',
            'command.ses-template.get-template'
        );
    }
}
