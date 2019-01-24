<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Aws\Ses\SesClient;
use Illuminate\Foundation\Application;
use Illuminate\Mail\TransportManager;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Sunaoka\LaravelSesTemplateDriver\SesTemplateTransportServiceProvider;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;

class SesTemplateTransportServiceProviderTest extends TestCase
{
    public function testRegisterDriver()
    {
        /** @var Application $app */
        $app = [
            'config' => new Collection([
                'mail.driver'  => 'ses.template',
                'services.ses' => [
                    'key'    => 'foo',
                    'secret' => 'bar',
                    'region' => 'us-east-1',
                ],
            ]),
        ];

        $manager = new TransportManager($app);

        $provider = new SesTemplateTransportServiceProvider($app);
        $provider->registerTransport($manager);

        /** @var SesTemplateTransport $transport */
        $transport = $manager->driver();

        $this->assertInstanceOf(SesTemplateTransport::class, $transport);

        /** @var SesClient $ses */
        $ses = $this->readAttribute($transport, 'ses');

        $this->assertEquals('us-east-1', $ses->getRegion());
    }
}
