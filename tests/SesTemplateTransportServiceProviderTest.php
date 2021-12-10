<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\MailServiceProvider;
use ReflectionException;
use Sunaoka\LaravelSesTemplateDriver\SesTemplateTransportServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;

class SesTemplateTransportServiceProviderTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testRegister(): void
    {
        $app = new Container();
        $app->singleton('config', function () {
            return new Repository([
                'mail.default'             => 'sestemplate',
                'mail.mailers.sestemplate' => ['transport' => 'sestemplate'],
                'services'                 => [
                    'ses' => [
                        'key'    => 'foo',
                        'secret' => 'bar',
                        'region' => 'us-east-1',
                    ],
                ],
            ]);
        });

        /** @var Application $app */
        (new SesTemplateTransportServiceProvider($app))->register();
        $this->callRestrictedMethod(new MailServiceProvider($app), 'registerIlluminateMailer');

        self::assertInstanceOf(MailManager::class, $app['mail.manager']);
    }

    public function testRegisterTransport(): void
    {
        $app = new Container();
        $app->singleton('config', function () {
            return new Repository([
                'mail.default'             => 'sestemplate',
                'mail.mailers.sestemplate' => ['transport' => 'sestemplate'],
                'services'                 => [
                    'ses' => [
                        'key'    => 'foo',
                        'secret' => 'bar',
                        'region' => 'us-east-1',
                    ],
                ],
            ]);
        });

        $app->singleton('view', function () {
            return new MockViewFactory();
        });

        $app->singleton('events', function () {
            return null;
        });

        /** @var Application $app */
        $manager = new MailManager($app);

        $provider = new SesTemplateTransportServiceProvider($app);
        $provider->registerTransport($manager);

        /** @var SesTemplateTransport $transport */
        $transport = $manager->getSymfonyTransport();
        self::assertInstanceOf(SesTemplateTransport::class, $transport);
        self::assertSame('sestemplate', (string)$transport);

        $ses = $transport->ses();

        self::assertEquals('us-east-1', $ses->getRegion());
    }

}

class MockViewFactory implements Factory
{
    public function exists($view)
    {
    }

    public function file($path, $data = [], $mergeData = [])
    {
    }

    public function make($view, $data = [], $mergeData = [])
    {
    }

    public function share($key, $value = null)
    {
    }

    public function composer($views, $callback)
    {
    }

    public function creator($views, $callback)
    {
    }

    public function addNamespace($namespace, $hints)
    {
    }

    public function replaceNamespace($namespace, $hints)
    {
    }
}
