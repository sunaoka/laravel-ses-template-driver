<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver;

use Illuminate\Foundation\Application;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Commands\GetTemplateCommand;
use Sunaoka\LaravelSesTemplateDriver\Commands\ListTemplatesCommand;
use Sunaoka\LaravelSesTemplateDriver\Services\SesServiceInterface;
use Sunaoka\LaravelSesTemplateDriver\Services\SesV1Service;
use Sunaoka\LaravelSesTemplateDriver\Services\SesV2Service;
use Sunaoka\LaravelSesTemplateDriver\Traits\TransportTrait;

class SesTemplateTransportServiceProvider extends ServiceProvider
{
    use TransportTrait;

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
        $manager->extend('sestemplate', function ($config) {
            return $this->createSesTemplateTransport($config);
        });

        $manager->extend('sesv2template', function ($config) {
            return $this->createSesV2TemplateTransport($config);
        });
    }

    /**
     * Register Commands
     */
    public function registerCommands(): void
    {
        $this->app->singleton('command.ses-template.list-templates', function (Application $app) {
            return new ListTemplatesCommand($this->resolveSesService($app));
        });

        $this->app->singleton('command.ses-template.get-template', function (Application $app) {
            return new GetTemplateCommand($this->resolveSesService($app));
        });

        $this->commands(
            'command.ses-template.list-templates',
            'command.ses-template.get-template',
        );
    }

    private function resolveSesService(Application $app): SesServiceInterface
    {
        $transport = $app['config']->get('mail.mailers.sestemplate.transport', 'sestemplate');
        if ($transport === 'sesv2template') {
            return new SesV2Service($this->createSesV2Client());
        }

        return new SesV1Service($this->createSesClient());
    }
}
