<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Mail;

use Illuminate\Mail\Mailables\Address;

class SesTemplateOptions
{
    /**
     * @param  array<string, string>|null  $headers
     */
    public function __construct(
        public ?Address $from = null,
        public ?Address $replyTo = null,
        public ?array $headers = null
    ) {}

    public function from(Address $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function replyTo(Address $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }
}
