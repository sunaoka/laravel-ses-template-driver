<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sunaoka\LaravelSesTemplateDriver\SesTemplateTransportServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            SesTemplateTransportServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('mail.default', 'sestemplate');
        $app['config']->set('mail.mailers.sestemplate', ['transport' => 'sestemplate']);
        $app['config']->set('services.ses', [
            'key'    => 'foo',
            'secret' => 'bar',
            'region' => 'us-east-2',
        ]);
    }
}
