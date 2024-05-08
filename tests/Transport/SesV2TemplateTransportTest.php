<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Transport;

use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\SesV2\Exception\SesV2Exception;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;
use Sunaoka\LaravelSesTemplateDriver\Traits\TransportTrait;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SesV2TemplateTransportTest extends TestCase
{
    use TransportTrait;

    /**
     * @throws \JsonException
     * @throws TransportExceptionInterface
     */
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
            ->sender(new Address('myself@example.com', 'Joe Q. Public'))
            ->to(new Address('me@example.com'))
            ->cc(new Address('cc@example.com'))
            ->bcc(new Address('bcc@example.com'))
            ->replyTo(new Address('reply-to@example.com'))
            ->subject('TemplateName')
            ->html((string) json_encode($templateData, JSON_THROW_ON_ERROR))
            ->attach('attach')
            ->embed('embed');

        $messageId = '0123456789abcdef-01234567-0123-0123-0123-0123456789ab-000000';

        $message->getHeaders()->add(new MetadataHeader('X-Custom-Header', 'Custom Value'));

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => $messageId]));

        config(['services.ses.handler' => $mockHandler]);

        $transport = $this->createSesV2TemplateTransport();

        $originalMessageId = $message->generateMessageId();
        $message->getHeaders()->addIdHeader('Message-ID', $originalMessageId);

        $actual = $transport->send($message);

        self::assertNotNull($actual);
        self::assertSame($messageId, $actual->getMessageId());
        self::assertInstanceOf(Email::class, $actual->getOriginalMessage());

        /** @var Email $originalMessage */
        $originalMessage = $actual->getOriginalMessage();

        self::assertSame($messageId, $originalMessage->getHeaders()->get('X-Message-ID')?->getBody());
        self::assertSame($messageId, $originalMessage->getHeaders()->get('X-SES-Message-ID')?->getBody());
        self::assertSame($originalMessageId, $originalMessage->getHeaders()->get('X-Original-Message-ID')?->getBody());

        $actual = $mockHandler->getLastCommand()->toArray();

        self::assertSame('MyConfigurationSet', $actual['ConfigurationSetName']);
        self::assertSame('foo', $actual['Tags'][0]['Name']);
        self::assertSame('bar', $actual['Tags'][0]['Value']);
        self::assertSame('"Joe Q. Public" <myself@example.com>', $actual['FromEmailAddress']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Content']['Template']['TemplateName']);
        self::assertSame(json_encode($templateData, JSON_THROW_ON_ERROR), $actual['Content']['Template']['TemplateData']);
        self::assertSame([['Name' => 'X-Custom-Header', 'Value' => 'Custom Value']], $actual['Content']['Template']['Headers']);
    }

    /**
     * @throws \JsonException
     * @throws TransportExceptionInterface
     */
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
            ->from(new Address('myself@example.com', 'Giant; "Big" Box'))
            ->to(new Address('me@example.com'))
            ->cc(new Address('cc@example.com'))
            ->bcc(new Address('bcc@example.com'))
            ->replyTo(new Address('reply-to@example.com'))
            ->subject('TemplateName')
            ->html((string) json_encode($templateData, JSON_THROW_ON_ERROR))
            ->attach('attach')
            ->embed('embed');

        $messageId = '0123456789abcdef-01234567-0123-0123-0123-0123456789ab-000000';

        $message->getHeaders()->add(new MetadataHeader('X-Custom-Header', 'Custom Value'));

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['MessageId' => $messageId]));

        config(['services.ses.handler' => $mockHandler]);

        $transport = $this->createSesV2TemplateTransport();

        $originalMessageId = $message->generateMessageId();
        $message->getHeaders()->addIdHeader('Message-ID', $originalMessageId);

        $actual = $transport->send($message);

        self::assertNotNull($actual);
        self::assertSame($messageId, $actual->getMessageId());
        self::assertInstanceOf(Email::class, $actual->getOriginalMessage());

        /** @var Email $originalMessage */
        $originalMessage = $actual->getOriginalMessage();

        self::assertSame($messageId, $originalMessage->getHeaders()->get('X-Message-ID')?->getBody());
        self::assertSame($messageId, $originalMessage->getHeaders()->get('X-SES-Message-ID')?->getBody());
        self::assertSame($originalMessageId, $originalMessage->getHeaders()->get('X-Original-Message-ID')?->getBody());

        $actual = $mockHandler->getLastCommand()->toArray();

        self::assertSame('MyConfigurationSet', $actual['ConfigurationSetName']);
        self::assertSame('foo', $actual['Tags'][0]['Name']);
        self::assertSame('bar', $actual['Tags'][0]['Value']);
        self::assertSame('"Giant; \"Big\" Box" <myself@example.com>', $actual['FromEmailAddress']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Content']['Template']['TemplateName']);
        self::assertSame(json_encode($templateData, JSON_THROW_ON_ERROR), $actual['Content']['Template']['TemplateData']);
        self::assertSame([['Name' => 'X-Custom-Header', 'Value' => 'Custom Value']], $actual['Content']['Template']['Headers']);
    }

    /**
     * @throws \JsonException
     * @throws TransportExceptionInterface
     */
    public function testFailed(): void
    {
        $message = (new Email())
            ->sender(new Address('myself@example.com', 'Joe Q. Public'))
            ->to(new Address('me@example.com'))
            ->subject('TemplateName')
            ->html((string) json_encode([], JSON_THROW_ON_ERROR));

        $mockHandler = new MockHandler();
        $mockHandler->append(static function (CommandInterface $cmd) {
            return new SesV2Exception('', $cmd, [
                'errorType' => 'client',
                'code' => 'NotFoundException',
                'message' => 'Template MyTemplate does not exist.',
            ]);
        });

        config(['services.ses.handler' => $mockHandler]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request to Amazon SES API v2 failed. Reason: Template MyTemplate does not exist.');

        $this->createSesV2TemplateTransport()->send($message);
    }

    public function testToString(): void
    {
        $transport = $this->createSesV2TemplateTransport();

        self::assertSame('sesv2template', (string) $transport);
    }

    public function testSes(): void
    {
        $transport = $this->createSesV2TemplateTransport();

        self::assertSame('us-east-2', $transport->ses()->getRegion());
    }
}
