<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Aws\MockHandler;
use Aws\Result;
use Aws\Ses\SesClient;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SesTemplateTransportTest extends TestCase
{
    public function testSendBySender(): void
    {
        $message = (new Email())
            ->sender(new Address('myself@example.com', 'myself'))
            ->to(new Address('me@example.com'))
            ->cc(new Address('cc@example.com'))
            ->bcc(new Address('bcc@example.com'))
            ->replyTo(new Address('reply-to@example.com'))
            ->subject('TemplateName')
            ->html(json_encode(['foo' => 'bar']));

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => 'xxx']));

        $client = new SesClient([
            'credentials' => [
                'key'    => 'key',
                'secret' => 'secret',
            ],
            'region'      => 'us-west-1',
            'version'     => 'latest',
            'handler'     => $mockHandler,
        ]);

        $transport = new SesTemplateTransport($client);

        $actual = $transport->send($message);

        self::assertNotNull($actual);

        $actual = $mockHandler->getLastCommand()->toArray();

        self::assertSame('myself <myself@example.com>', $actual['Source']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Template']);
        self::assertSame(json_encode(['foo' => 'bar']), $actual['TemplateData']);
    }

    public function testSendByFrom(): void
    {
        $message = (new Email())
            ->from(new Address('myself@example.com', 'myself'))
            ->to(new Address('me@example.com'))
            ->cc(new Address('cc@example.com'))
            ->bcc(new Address('bcc@example.com'))
            ->replyTo(new Address('reply-to@example.com'))
            ->subject('TemplateName')
            ->html(json_encode(['foo' => 'bar']));

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => 'xxx']));

        $client = new SesClient([
            'credentials' => [
                'key'    => 'key',
                'secret' => 'secret',
            ],
            'region'      => 'us-west-1',
            'version'     => 'latest',
            'handler'     => $mockHandler,
        ]);

        $transport = new SesTemplateTransport($client);

        $actual = $transport->send($message);

        self::assertNotNull($actual);

        $actual = $mockHandler->getLastCommand()->toArray();

        self::assertSame('myself <myself@example.com>', $actual['Source']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Template']);
        self::assertSame(json_encode(['foo' => 'bar']), $actual['TemplateData']);
    }
}
