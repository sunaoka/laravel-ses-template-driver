<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Transport;

use Aws\Ses\SesClient;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SesTemplateTransport extends AbstractTransport
{
    /**
     * Create a new SES transport instance.
     *
     * @param SesClient                     $ses
     * @param array                         $options
     * @param EventDispatcherInterface|null $dispatcher
     * @param LoggerInterface|null          $logger
     */
    public function __construct(
        protected SesClient $ses,
        protected array $options = [],
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);
    }

    /**
     * @param SentMessage $message
     *
     * @return void
     */
    protected function doSend(SentMessage $message): void
    {
        /** @var Email $originalMessage */
        $originalMessage = $message->getOriginalMessage();

        $args = [
            'Destination'      => [
                'ToAddresses'  => $this->stringifyAddresses($originalMessage->getTo()),
                'CcAddresses'  => $this->stringifyAddresses($originalMessage->getCc()),
                'BccAddresses' => $this->stringifyAddresses($originalMessage->getBcc()),
            ],
            'ReplyToAddresses' => $this->stringifyAddresses($originalMessage->getReplyTo()),
            'Source'           => $this->getMailbox($message->getEnvelope()->getSender()),
            'Template'         => $originalMessage->getSubject(),
            'TemplateData'     => $originalMessage->getHtmlBody(),
        ];

        $args = array_merge($this->options, $args);

        /** @var array{MessageId: string} $result */
        $result = $this->ses->sendTemplatedEmail($args);

        $originalMessage->getHeaders()->addTextHeader('X-SES-Message-ID', $result['MessageId']);
    }

    /**
     * @param Address[] $addresses
     *
     * @return string[]
     */
    protected function stringifyAddresses(array $addresses): array
    {
        return array_map(function (Address $address) {
            return $this->getMailbox($address);
        }, $addresses);
    }

    /**
     * @param Address $address
     *
     * @return string
     */
    private function getMailbox(Address $address): string
    {
        if ($address->getName() === '') {
            return $address->getEncodedAddress();
        }
        return sprintf('%s <%s>', mb_encode_mimeheader($address->getName()), $address->getEncodedAddress());
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'sestemplate';
    }

    /**
     * Get the Amazon SES client for the SesTransport instance.
     *
     * @return SesClient
     */
    public function ses(): SesClient
    {
        return $this->ses;
    }
}
