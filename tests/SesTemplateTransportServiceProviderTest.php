<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Aws\Ses\SesClient;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Collection;
use Sunaoka\LaravelSesTemplateDriver\SesTemplateTransportServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;

class SesTemplateTransportServiceProviderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testRegister()
    {
        $app = new Container();
        $app->singleton('config', function () {
            return new Repository([
                'mail.default' => 'sestemplate',
                'mail.mailers.sestemplate' => ['transport' => 'sestemplate'],
                'services' => [
                    'ses' => [
                        'key'    => 'foo',
                        'secret' => 'bar',
                        'region' => 'us-east-1',
                    ],
                ],
            ]);
        });

        (new SesTemplateTransportServiceProvider($app))->register();
        $this->callRestrictedMethod(new MailServiceProvider($app), 'registerIlluminateMailer');

        $this->assertInstanceOf(MailManager::class, $app['mail.manager']);
    }

    public function testRegisterDriver()
    {
        $app = new Container();
        $app->singleton('config', function () {
            return new Repository([
                'mail.default' => 'sestemplate',
                'mail.mailers.sestemplate' => ['transport' => 'sestemplate'],
                'services' => [
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

        $manager = new MailManager($app);

        $provider = new SesTemplateTransportServiceProvider($app);
        $provider->registerTransport($manager);

        /** @var SesTemplateTransport $transport */
        $transport = $manager->getSwiftMailer()->getTransport();
        $this->assertInstanceOf(SesTemplateTransport::class, $transport);

        /** @var SesClient $ses */
        $ses = $transport->ses();

        $this->assertEquals('us-east-1', $ses->getRegion());
    }

}

class MockViewFactory implements \Illuminate\Contracts\View\Factory
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
