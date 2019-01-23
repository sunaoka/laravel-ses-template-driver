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
    private $template;

    /**
     * @var array
     */
    private $templateData;

    /**
     * Create a new message instance.
     *
     * @param string $template
     * @param array  $templateData
     */
    public function __construct(string $template, array $templateData)
    {
        $this->template = $template;
        $this->templateData = $templateData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->template)->html(json_encode($this->templateData));
    }
}
