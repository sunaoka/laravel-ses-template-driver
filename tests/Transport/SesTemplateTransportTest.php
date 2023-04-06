<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Transport;

use Aws\MockHandler;
use Aws\Result;
use Illuminate\Support\Facades\Config;
use Sunaoka\LaravelSesTemplateDriver\Helper;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SesTemplateTransportTest extends TestCase
{
    public function testSendBySender(): void
    {
        $templateData = [
            'foo1' => 'bar1',
            'foo2' => 'bar2',
            'foo3' => 'bar3',
            'foo4' => 'bar4',
            'foo5' => 'bar5',
            'foo6' => 'bar6',
            'foo7' => 'bar7',
            'foo8' => 'bar8',
            'foo9' => 'bar9',
        ];

        $message = (new Email())
            ->sender(new Address('myself@example.com', 'myself'))
            ->to(new Address('me@example.com'))
            ->cc(new Address('cc@example.com'))
            ->bcc(new Address('bcc@example.com'))
            ->replyTo(new Address('reply-to@example.com'))
            ->subject('TemplateName')
            ->html((string)json_encode($templateData))
            ->attach('attach')
            ->embed('embed');

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => 'xxx']));

        Config::set('services.ses.handler', $mockHandler);

        $transport = (new Helper())->createTransport();

        $actual = $transport->send($message);

        self::assertNotNull($actual);

        $actual = $mockHandler->getLastCommand()->toArray();

        self::assertSame('MyConfigurationSet', $actual['ConfigurationSetName']);
        self::assertSame('foo', $actual['Tags'][0]['Name']);
        self::assertSame('bar', $actual['Tags'][0]['Value']);
        self::assertSame('myself <myself@example.com>', $actual['Source']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Template']);
        self::assertSame(json_encode($templateData), $actual['TemplateData']);
    }

    public function testSendByFrom(): void
    {
        $templateData = [
            'foo1' => 'bar1',
            'foo2' => 'bar2',
            'foo3' => 'bar3',
            'foo4' => 'bar4',
            'foo5' => 'bar5',
            'foo6' => 'bar6',
            'foo7' => 'bar7',
            'foo8' => 'bar8',
            'foo9' => 'bar9',
        ];

        $message = (new Email())
            ->from(new Address('myself@example.com', 'myself'))
            ->to(new Address('me@example.com'))
            ->cc(new Address('cc@example.com'))
            ->bcc(new Address('bcc@example.com'))
            ->replyTo(new Address('reply-to@example.com'))
            ->subject('TemplateName')
            ->html((string)json_encode($templateData))
            ->attach('attach')
            ->embed('embed');

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => 'xxx']));

        Config::set('services.ses.handler', $mockHandler);

        $transport = (new Helper())->createTransport();

        $actual = $transport->send($message);

        self::assertNotNull($actual);

        $actual = $mockHandler->getLastCommand()->toArray();

        self::assertSame('MyConfigurationSet', $actual['ConfigurationSetName']);
        self::assertSame('foo', $actual['Tags'][0]['Name']);
        self::assertSame('bar', $actual['Tags'][0]['Value']);
        self::assertSame('myself <myself@example.com>', $actual['Source']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Template']);
        self::assertSame(json_encode($templateData), $actual['TemplateData']);
    }

    public function testToString(): void
    {
        /** @var SesTemplateTransport $transport */
        $transport = (new Helper())->createTransport();

        self::assertSame('sestemplate', (string)$transport);
    }

    public function testSes(): void
    {
        /** @var SesTemplateTransport $transport */
        $transport = (new Helper())->createTransport();

        self::assertSame('us-east-2', $transport->ses()->getRegion());
    }
}
