<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Transport;

use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\Ses\Exception\SesException;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;
use Sunaoka\LaravelSesTemplateDriver\Traits\TransportTrait;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SesTemplateTransportTest extends TestCase
{
    use TransportTrait;

    /**
     * @throws \JsonException
     * @throws TransportExceptionInterface
     */
    public function test_send_by_sender(): void
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

        $message = (new Email)
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

        $mockHandler = new MockHandler;
        $mockHandler->append(new Result(['MessageId' => $messageId]));

        config(['services.ses.handler' => $mockHandler]);

        $transport = $this->createSesTemplateTransport();

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
        self::assertSame('"Joe Q. Public" <myself@example.com>', $actual['Source']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Template']);
        self::assertSame(json_encode($templateData, JSON_THROW_ON_ERROR), $actual['TemplateData']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function test_send_by_from(): void
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

        $message = (new Email)
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

        $mockHandler = new MockHandler;
        $mockHandler->append(new Result(['MessageId' => $messageId]));

        config(['services.ses.handler' => $mockHandler]);

        $transport = $this->createSesTemplateTransport();

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
        self::assertSame('"Giant; \"Big\" Box" <myself@example.com>', $actual['Source']);
        self::assertSame(['me@example.com'], $actual['Destination']['ToAddresses']);
        self::assertSame(['cc@example.com'], $actual['Destination']['CcAddresses']);
        self::assertSame(['bcc@example.com'], $actual['Destination']['BccAddresses']);
        self::assertSame(['reply-to@example.com'], $actual['ReplyToAddresses']);
        self::assertSame('TemplateName', $actual['Template']);
        self::assertSame(json_encode($templateData, JSON_THROW_ON_ERROR), $actual['TemplateData']);
    }

    /**
     * @throws \JsonException
     * @throws TransportExceptionInterface
     */
    public function test_failed(): void
    {
        $message = (new Email)
            ->sender(new Address('myself@example.com', 'Joe Q. Public'))
            ->to(new Address('me@example.com'))
            ->subject('TemplateName')
            ->html((string) json_encode([], JSON_THROW_ON_ERROR));

        $mockHandler = new MockHandler;
        $mockHandler->append(static function (CommandInterface $cmd) {
            return new SesException('', $cmd, [
                'errorType' => 'client',
                'code' => 'NotFoundException',
                'message' => 'Template MyTemplate does not exist.',
            ]);
        });

        config(['services.ses.handler' => $mockHandler]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request to Amazon SES API failed. Reason: Template MyTemplate does not exist.');

        $this->createSesTemplateTransport()->send($message);
    }

    public function test_to_string(): void
    {
        $transport = $this->createSesTemplateTransport();

        self::assertSame('sestemplate', (string) $transport);
    }

    public function test_ses(): void
    {
        $transport = $this->createSesTemplateTransport();

        self::assertSame('us-east-2', $transport->ses()->getRegion());
    }
}
