<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Aws\MockHandler;
use Aws\Result;
use Aws\Ses\SesClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Sunaoka\LaravelSesTemplateDriver\Helper;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Swift_Message;

class SesTemplateTransportTest extends TestCase
{
    public function testSendBySender(): void
    {
        $message = new Swift_Message('TemplateName', json_encode(['foo' => 'bar']));
        $message->setSender('myself@example.com', 'myself');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('reply-to@example.com');

        $messageId = Str::random(32);

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => $messageId]));

        Config::set('services.ses.handler', $mockHandler);

        $transport = (new Helper($this->app))->createTransport();

        $actual = $transport->send($message);

        self::assertSame(3, $actual);

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
        self::assertSame(json_encode(['foo' => 'bar']), $actual['TemplateData']);

        self::assertSame($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }

    public function testSendByFrom(): void
    {
        $message = new Swift_Message('TemplateName', json_encode(['foo' => 'bar']));
        $message->setFrom('myself@example.com', 'myself');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('reply-to@example.com');

        $messageId = Str::random(32);

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => $messageId]));

        Config::set('services.ses.handler', $mockHandler);

        $transport = (new Helper($this->app))->createTransport();

        $actual = $transport->send($message);

        self::assertSame(3, $actual);

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
        self::assertSame(json_encode(['foo' => 'bar']), $actual['TemplateData']);

        self::assertSame($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }

    public function testSes(): void
    {
        $client = new SesClient([
            'credentials' => [
                'key'    => 'key',
                'secret' => 'secret',
            ],
            'region'      => 'us-west-1',
            'version'     => 'latest',
        ]);

        $transport = new SesTemplateTransport($client);

        self::assertSame('us-west-1', $transport->ses()->getRegion());
    }
}
