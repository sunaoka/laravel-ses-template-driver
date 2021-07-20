<?php

namespace Sunaoka\LaravelSesTemplateDriver\Transport;

use Aws\Ses\SesClient;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

class SesTemplateTransport extends Transport
{
    /**
     * The Amazon SES instance.
     *
     * @var SesClient
     */
    protected $ses;

    /**
     * The Amazon SES transmission options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SES transport instance.
     *
     * @param SesClient $ses
     * @param array     $options
     *
     * @return void
     */
    public function __construct(SesClient $ses, $options = [])
    {
        $this->ses = $ses;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $this->beforeSendPerformed($message);

        $template = $message->getSubject();
        $templateData = $message->getBody();

        $destination['ToAddresses'] = array_keys($message->getTo());

        if (! is_null($message->getCc())) {
            $destination['CcAddresses'] = array_keys($message->getCc());
        }
        if (! is_null($message->getBcc())) {
            $destination['BccAddresses'] = array_keys($message->getBcc());
        }

        $from = $message->getSender() ?: $message->getFrom();
        $mailAddress = key($from);
        $source = sprintf('%s <%s>', mb_encode_mimeheader($from[$mailAddress]), $mailAddress);

        $args = [
            'Destination'  => $destination,
            'Source'       => $source,
            'Template'     => $template,
            'TemplateData' => $templateData,
        ];

        if (! is_null($message->getReplyTo())) {
            $args['ReplyToAddresses'] = array_keys($message->getReplyTo());
        }

        $args = array_merge($this->options, $args);

        $result = $this->ses->sendTemplatedEmail($args);

        $message->getHeaders()->addTextHeader('X-SES-Message-ID', $result->get('MessageId'));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
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
