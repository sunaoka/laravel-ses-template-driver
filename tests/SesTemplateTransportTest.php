<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Aws\Ses\SesClient;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockBuilder;
use Sunaoka\LaravelSesTemplateDriver\Transport\SesTemplateTransport;
use Swift_Message;

class SesTemplateTransportTest extends TestCase
{
    public function testSendBySender()
    {
        $message = new Swift_Message('TemplateName', ['foo' => 'bar']);
        $message->setSender('myself@example.com', 'myself');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('reply-to@example.com');

        /** @var SesClient|MockBuilder $client */
        $client = $this->getMockBuilder(SesClient::class)
            ->setMethods(['sendTemplatedEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $transport = new SesTemplateTransport($client);

        $messageId = Str::random(32);
        $sendTemplatedEmailMock = new sendTemplatedEmailMock($messageId);
        $client->expects($this->once())
            ->method('sendTemplatedEmail')
            ->with($this->equalTo([
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
        $this->assertEquals($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }

    public function testSendByFrom()
    {
        $message = new Swift_Message('TemplateName', ['foo' => 'bar']);
        $message->setFrom('myself@example.com', 'myself');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('reply-to@example.com');

        /** @var SesClient|MockBuilder $client */
        $client = $this->getMockBuilder(SesClient::class)
            ->setMethods(['sendTemplatedEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $transport = new SesTemplateTransport($client);

        $messageId = Str::random(32);
        $sendTemplatedEmailMock = new sendTemplatedEmailMock($messageId);
        $client->expects($this->once())
            ->method('sendTemplatedEmail')
            ->with($this->equalTo([
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
        $this->assertEquals($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
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
    public function get(string $key)
    {
        return $this->getResponse;
    }
}
