<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Commands\GetTemplateCommand;
use Sunaoka\LaravelSesTemplateDriver\Commands\ListTemplatesCommand;

class SesTemplateTransportServiceProvider extends ServiceProvider
{
    /**
     * Register the Transport instance.
     */
    public function register(): void
    {
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $this->registerTransport($manager);
        });

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register Transport
     */
    public function registerTransport(MailManager $manager): void
    {
        $manager->extend('sestemplate', function () {
            return (new Helper())->createTransport();
        });
    }

    /**
     * Register Commands
     */
    public function registerCommands(): void
    {
        $this->app->singleton('command.ses-template.list-templates', function ($app) {
            return new ListTemplatesCommand((new Helper())->createClient());
        });

        $this->app->singleton('command.ses-template.get-template', function ($app) {
            return new GetTemplateCommand((new Helper())->createClient());
        });

        $this->commands(
            'command.ses-template.list-templates',
            'command.ses-template.get-template',
        );
    }
}
