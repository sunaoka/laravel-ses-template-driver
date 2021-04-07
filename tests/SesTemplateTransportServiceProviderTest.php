<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Mail\TransportManager;
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
                'mail.driver'  => 'ses.template',
                'services.ses' => [
                    'key'    => 'foo',
                    'secret' => 'bar',
                    'region' => 'us-east-1',
                ],
            ]);
        });

        /** @var Application $app */
        (new SesTemplateTransportServiceProvider($app))->register();
        $this->callRestrictedMethod(new MailServiceProvider($app), 'registerSwiftTransport');

        self::assertInstanceOf(TransportManager::class, $app['swift.transport']);
    }

    public function testRegisterDriver(): void
    {
        $app = new Container();
        $app->singleton('config', function () {
            return new Repository([
                'mail.driver'  => 'ses.template',
                'services.ses' => [
                    'key'    => 'foo',
                    'secret' => 'bar',
                    'region' => 'us-east-1',
                ],
            ]);
        });

        $manager = new TransportManager($app);

        /** @var Application $app */
        $provider = new SesTemplateTransportServiceProvider($app);
        $provider->registerTransport($manager);

        /** @var SesTemplateTransport $transport */
        $transport = $manager->driver();

        self::assertInstanceOf(SesTemplateTransport::class, $transport);

        $ses = $transport->ses();

        self::assertSame('us-east-1', $ses->getRegion());
    }
}
