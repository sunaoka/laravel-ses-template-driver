<?php

namespace Sunaoka\LaravelSesTemplateDriver\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SesTemplate extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var array
     */
    private $templateData;

    /**
     * @var array
     */
    private $options;

    /**
     * Create a new message instance.
     *
     * @param string $templateName Template Name
     * @param array  $templateData Template Data
     * @param array  $options      Options
     */
    public function __construct(string $templateName, array $templateData, array $options = [])
    {
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        $this->options = $options;
    }

    /**
     * Build the message.
     *
     * @return self
     */
    public function build(): self
    {
        if (isset($this->options['from']['address'])) {
            $this->from($this->options['from']['address'], $this->options['from']['name'] ?? null);
        }

        if (isset($this->options['reply_to']['address'])) {
            $this->replyTo($this->options['reply_to']['address'], $this->options['reply_to']['name'] ?? null);
        }

        return $this->subject($this->templateName)->html(json_encode($this->templateData));
    }

    /**
     * Get the transmission template name being used by the transport.
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * Set the transmission template name being used by the transport.
     *
     * @param string $templateName
     */
    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    /**
     * Get the transmission template data being used by the transport.
     *
     * @return array
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * Set the transmission template data being used by the transport.
     *
     * @param array $templateData
     */
    public function setTemplateData(array $templateData): void
    {
        $this->templateData = $templateData;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
