<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class SesTemplate extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  string  $templateName  Template Name
     * @param  array  $templateData  Template Data
     */
    public function __construct(
        private string $templateName,
        private array $templateData,
        private SesTemplateOptions $options = new SesTemplateOptions(),
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     *
     * @throws \JsonException
     */
    public function build(): self
    {
        if ($this->options->from !== null) {
            $this->from($this->options->from);
        }

        if ($this->options->replyTo !== null) {
            $this->replyTo($this->options->replyTo);
        }

        if ($this->options->headers !== null && Arr::isAssoc($this->options->headers)) {
            foreach ($this->options->headers as $key => $value) {
                if (is_string($value)) {
                    $this->metadata((string) $key, $value);
                }
            }
        }

        return $this->subject($this->templateName)->html((string) json_encode($this->templateData, JSON_THROW_ON_ERROR));
    }

    /**
     * Get the transmission template name being used by the transport.
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * Set the transmission template name being used by the transport.
     */
    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    /**
     * Get the transmission template data being used by the transport.
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * Set the transmission template data being used by the transport.
     */
    public function setTemplateData(array $templateData): void
    {
        $this->templateData = $templateData;
    }

    /**
     * Get the transmission options being used by the transport.
     */
    public function getOptions(): SesTemplateOptions
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     */
    public function setOptions(SesTemplateOptions $options): void
    {
        $this->options = $options;
    }
}
