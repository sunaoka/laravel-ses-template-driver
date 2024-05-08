<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Transport;

use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class SesTemplateTransport extends AbstractTransport
{
    /**
     * Create a new SES transport instance.
     */
    public function __construct(
        protected SesClient $ses,
        protected array $options = []
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        /** @var Email $email */
        $email = $message->getOriginalMessage();

        try {
            $args = array_merge($this->options, [
                'Destination' => [
                    'ToAddresses' => $this->stringifyAddresses($email->getTo()),
                    'CcAddresses' => $this->stringifyAddresses($email->getCc()),
                    'BccAddresses' => $this->stringifyAddresses($email->getBcc()),
                ],
                'ReplyToAddresses' => $this->stringifyAddresses($email->getReplyTo()),
                'Source' => $message->getEnvelope()->getSender()->toString(),
                'Template' => $email->getSubject(),
                'TemplateData' => $email->getHtmlBody(),
            ]);

            $result = $this->ses->sendTemplatedEmail($args);
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new \RuntimeException(
                sprintf('Request to Amazon SES API failed. Reason: %s', $reason),
                $e->getCode(),
                $e
            );
        }

        /** @var string $messageId */
        $messageId = $result->get('MessageId');

        $email->getHeaders()->addHeader('X-Message-ID', $messageId);
        $email->getHeaders()->addHeader('X-SES-Message-ID', $messageId);

        $email->getHeaders()->addHeader('X-Original-Message-ID', $message->getMessageId());

        $message->setMessageId($messageId);
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'sestemplate';
    }

    /**
     * Get the Amazon SES client for the SesTransport instance.
     */
    public function ses(): SesClient
    {
        return $this->ses;
    }
}
