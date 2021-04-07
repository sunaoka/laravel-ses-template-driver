<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Aws\Ses\SesClient;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Swift_Message;

class SesTemplateTransportTest extends TestCase
{
    public function testSendBySender(): void
    {
        $message = new Swift_Message('TemplateName', ['foo' => 'bar']);
        $message->setSender('myself@example.com', 'myself');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('reply-to@example.com');

        /** @var SesClient|MockObject $client */
        $client = $this->getMockBuilder(SesClient::class)
            ->addMethods(['sendTemplatedEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $transport = new SesTemplateTransport($client);

        $messageId = Str::random(32);
        $sendTemplatedEmailMock = new sendTemplatedEmailMock($messageId);
        $client->expects(self::once())
            ->method('sendTemplatedEmail')
            ->with(self::equalTo([
                'Source'           => 'myself <myself@example.com>',
                'Destination'      => [
                    'ToAddresses'  => ['me@example.com'],
                    'CcAddresses'  => ['cc@example.com'],
                    'BccAddresses' => ['bcc@example.com'],
                ],
                'ReplyToAddresses' => ['reply-to@example.com'],
                'Template'         => 'TemplateName',
                'TemplateData'     => ['foo' => 'bar'],
            ]))
            ->willReturn($sendTemplatedEmailMock);

        $transport->send($message);
        self::assertSame($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }

    public function testSendByFrom(): void
    {
        $message = new Swift_Message('TemplateName', ['foo' => 'bar']);
        $message->setFrom('myself@example.com', 'myself');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('reply-to@example.com');

        /** @var SesClient|MockObject $client */
        $client = $this->getMockBuilder(SesClient::class)
            ->addMethods(['sendTemplatedEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $transport = new SesTemplateTransport($client);

        $messageId = Str::random(32);
        $sendTemplatedEmailMock = new sendTemplatedEmailMock($messageId);
        $client->expects(self::once())
            ->method('sendTemplatedEmail')
            ->with(self::equalTo([
                'Source'           => 'myself <myself@example.com>',
                'Destination'      => [
                    'ToAddresses'  => ['me@example.com'],
                    'CcAddresses'  => ['cc@example.com'],
                    'BccAddresses' => ['bcc@example.com'],
                ],
                'ReplyToAddresses' => ['reply-to@example.com'],
                'Template'         => 'TemplateName',
                'TemplateData'     => ['foo' => 'bar'],
            ]))
            ->willReturn($sendTemplatedEmailMock);

        $transport->send($message);
        self::assertSame($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }
}

class sendTemplatedEmailMock
{
    protected $getResponse;

    public function __construct(string $responseValue)
    {
        $this->getResponse = $responseValue;
    }

    /**
     * Mock the get() call for the sendTemplatedEmail response.
     *
     * @param string $key
     *
     * @return string
     */
    public function get(string $key): string
    {
        return $this->getResponse;
    }
}
