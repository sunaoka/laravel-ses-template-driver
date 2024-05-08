<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Transport;

use Aws\Exception\AwsException;
use Aws\SesV2\SesV2Client;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class SesV2TemplateTransport extends AbstractTransport
{
    /**
     * Create a new SES transport instance.
     */
    public function __construct(
        protected SesV2Client $ses,
        protected array $options = []
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        /** @var Email $email */
        $email = $message->getOriginalMessage();

        $headers = [];
        foreach ($email->getHeaders()->all() as $header) {
            if ($header instanceof MetadataHeader) {
                $headers[] = ['Name' => $header->getKey(), 'Value' => $header->getValue()];
            }
        }

        try {
            $args = array_merge($this->options, [
                'Destination' => [
                    'ToAddresses' => $this->stringifyAddresses($email->getTo()),
                    'CcAddresses' => $this->stringifyAddresses($email->getCc()),
                    'BccAddresses' => $this->stringifyAddresses($email->getBcc()),
                ],
                'ReplyToAddresses' => $this->stringifyAddresses($email->getReplyTo()),
                'FromEmailAddress' => $message->getEnvelope()->getSender()->toString(),
                'Content' => [
                    'Template' => [
                        'Headers' => $headers,
                        'TemplateName' => $email->getSubject(),
                        'TemplateData' => $email->getHtmlBody(),
                    ],
                ],
            ]);

            $result = $this->ses->sendEmail($args);
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new \RuntimeException(
                sprintf('Request to Amazon SES API v2 failed. Reason: %s', $reason),
                $e->getCode(),
                $e
            );
        }

        /** @var string $messageId */
        $messageId = $result->get('MessageId');

        $message->setMessageId($messageId);

        $email->getHeaders()->addHeader('X-Message-ID', $messageId);
        $email->getHeaders()->addHeader('X-SES-Message-ID', $messageId);

        $email->getHeaders()->addHeader('X-Original-Message-ID', $message->getMessageId());
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'sesv2template';
    }

    /**
     * Get the Amazon SES v2 client for the SesTransport instance.
     */
    public function ses(): SesV2Client
    {
        return $this->ses;
    }
}
