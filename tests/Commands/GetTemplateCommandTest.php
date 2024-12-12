<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Commands;

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\MockHandler;
use Aws\Result;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class GetTemplateCommandTest extends TestCase
{
    protected function setSuccessMockHandler(array $template): void
    {
        $mockHandler = new MockHandler;
        $mockHandler->append(new Result($template));

        config(['services.ses.handler' => $mockHandler]);
    }

    protected function setFailureMockHandler(): void
    {
        $mockHandler = new MockHandler;
        $mockHandler->append(static function (CommandInterface $cmd) {
            return new AwsException('Template MyTemplate does not exist.', $cmd);
        });

        config(['services.ses.handler' => $mockHandler]);
    }

    #[DefineEnvironment('usesSesV1Transport')]
    public function test_ses_v1_invoke_text_success(): void
    {
        $template = [
            'Template' => [
                'TemplateName' => 'MyTemplate',
                'SubjectPart' => 'Greetings, {{name}}!',
                'TextPart' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
                'HtmlPart' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
            ],
        ];

        $this->setSuccessMockHandler($template);

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate'])
            ->expectsOutput('TemplateName:')
            ->expectsOutput($template['Template']['TemplateName'])
            ->expectsOutput('SubjectPart:')
            ->expectsOutput($template['Template']['SubjectPart'])
            ->expectsOutput('TextPart:')
            ->expectsOutput($template['Template']['TextPart'])
            ->expectsOutput('HtmlPart:')
            ->expectsOutput($template['Template']['HtmlPart'])
            ->assertSuccessful();
    }

    #[DefineEnvironment('usesSesV2Transport')]
    public function test_ses_v2_invoke_text_success(): void
    {
        $template = [
            'TemplateName' => 'MyTemplate',
            'TemplateContent' => [
                'Subject' => 'Greetings, {{name}}!',
                'Text' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
                'Html' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
            ],
        ];

        $this->setSuccessMockHandler($template);

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate'])
            ->expectsOutput('TemplateName:')
            ->expectsOutput($template['TemplateName'])
            ->expectsOutput('Subject:')
            ->expectsOutput($template['TemplateContent']['Subject'])
            ->expectsOutput('Text:')
            ->expectsOutput($template['TemplateContent']['Text'])
            ->expectsOutput('Html:')
            ->expectsOutput($template['TemplateContent']['Html'])
            ->assertSuccessful();
    }

    /**
     * @throws \JsonException
     */
    #[DefineEnvironment('usesSesV1Transport')]
    public function test_v1_invoke_json_success(): void
    {
        $template = [
            'Template' => [
                'TemplateName' => 'MyTemplate',
                'SubjectPart' => 'Greetings, {{name}}!',
                'HtmlPart' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
                'TextPart' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
            ],
        ];

        $this->setSuccessMockHandler($template);

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate', '--json' => true])
            ->expectsOutput(json_encode($template, JSON_THROW_ON_ERROR))
            ->assertSuccessful();
    }

    /**
     * @throws \JsonException
     */
    #[DefineEnvironment('usesSesV2Transport')]
    public function test_v2_invoke_json_success(): void
    {
        $template = [
            'TemplateName' => 'MyTemplate',
            'TemplateContent' => [
                'Subject' => 'Greetings, {{name}}!',
                'Text' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
                'Html' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
            ],
        ];

        $this->setSuccessMockHandler($template);

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate', '--json' => true])
            ->expectsOutput(json_encode($template, JSON_THROW_ON_ERROR))
            ->assertSuccessful();
    }

    public function test_invoke_text_failure(): void
    {
        $this->setFailureMockHandler();

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate'])
            ->assertFailed();
    }

    public function test_invoke_json_failure(): void
    {
        $this->setFailureMockHandler();

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate', '--json' => true])
            ->expectsOutput('null')
            ->assertFailed();
    }
}
