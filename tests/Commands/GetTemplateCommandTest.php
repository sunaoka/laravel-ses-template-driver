<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Commands;

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\MockHandler;
use Aws\Result;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\RequestInterface;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class GetTemplateCommandTest extends TestCase
{
    protected function setSuccessMockHandler(): array
    {
        $template = [
            'Template' => [
                'TemplateName' => 'MyTemplate',
                'SubjectPart' => 'Greetings, {{name}}!',
                'HtmlPart' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
                'TextPart' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
            ],
        ];

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result($template));

        Config::set('services.ses.handler', $mockHandler);

        return $template;
    }

    protected function setFailureMockHandler(): void
    {
        $mockHandler = new MockHandler();
        $mockHandler->append(function (CommandInterface $cmd, RequestInterface $req) {
            return new AwsException('Template MyTemplate does not exist.', $cmd);
        });

        Config::set('services.ses.handler', $mockHandler);
    }

    public function testInvokeTextSuccess(): void
    {
        $template = $this->setSuccessMockHandler();

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate'])
            ->expectsOutput('TemplateName:')
            ->expectsOutput($template['Template']['TemplateName'])
            ->expectsOutput('SubjectPart:')
            ->expectsOutput($template['Template']['SubjectPart'])
            ->expectsOutput('HtmlPart:')
            ->expectsOutput($template['Template']['HtmlPart'])
            ->expectsOutput('TextPart:')
            ->expectsOutput($template['Template']['TextPart'])
            ->assertSuccessful();
    }

    public function testInvokeJsonSuccess(): void
    {
        $template = $this->setSuccessMockHandler();

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate', '--json' => true])
            ->expectsOutput(json_encode($template))
            ->assertSuccessful();
    }

    public function testInvokeTextFailure(): void
    {
        $this->setFailureMockHandler();

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate'])
            ->assertFailed();
    }

    public function testInvokeJsonFailure(): void
    {
        $this->setFailureMockHandler();

        $this->artisan('ses-template:get-template', ['TemplateName' => 'MyTemplate', '--json' => true])
            ->expectsOutput('null')
            ->assertFailed();
    }
}
