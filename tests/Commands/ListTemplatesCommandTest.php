<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Commands;

use Aws\Api\DateTimeResult;
use Aws\MockHandler;
use Aws\Result;
use Illuminate\Support\Facades\Config;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class ListTemplatesCommandTest extends TestCase
{
    protected function setSuccessMockHandler(): array
    {
        $templatesMetadata = [
            [
                'Name'             => 'NewsAndUpdates',
                'CreatedTimestamp' => DateTimeResult::fromTimestamp('2021-10-03T20:03:34.574Z'),
            ],
            [
                'Name'             => 'SpecialOffers',
                'CreatedTimestamp' => DateTimeResult::fromTimestamp('2020-08-05T16:04:12.640Z'),
            ],
        ];

        $template = [
            'Template' => [
                'TemplateName' => 'MyTemplate',
                'SubjectPart'  => 'Greetings, {{name}}!',
                'HtmlPart'     => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
                'TextPart'     => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
            ],
        ];

        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['TemplatesMetadata' => [$templatesMetadata[0]], 'NextToken' => 'token']));
        $mockHandler->append(new Result(['TemplatesMetadata' => [$templatesMetadata[1]], 'NextToken' => null]));
        $mockHandler->append(new Result($template));

        Config::set('services.ses.handler', $mockHandler);

        return [
            'TemplatesMetadata' => array_map(static fn($template) => [
                'No'               => null,
                'Name'             => $template['Name'],
                'CreatedTimestamp' => (string)$template['CreatedTimestamp'],
            ], $templatesMetadata),
        ];
    }

    protected function setFailureMockHandler(): void
    {
        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['TemplatesMetadata' => [], 'NextToken' => null]));

        Config::set('services.ses.handler', $mockHandler);
    }

    /**
     * @dataProvider invokeTextSuccessProvider
     *
     * @param int[] $nums
     * @param array $options
     *
     * @return void
     */
    public function testInvokeTextSuccess(array $nums, array $options): void
    {
        $table = $this->setSuccessMockHandler();

        $table['TemplatesMetadata'][0]['No'] = $nums[0];
        $table['TemplatesMetadata'][1]['No'] = $nums[1];

        $this->artisan('ses-template:list-templates', $options)
            ->expectsOutput('.')
            ->expectsOutput('.')
            ->expectsOutput(' done.')
            ->expectsTable(['No', 'Name', 'CreatedTimestamp'], collect($table['TemplatesMetadata'])->sortBy('No')->all())
            ->expectsQuestion('Enter a number to display the template object', '0')
            ->assertSuccessful();
    }

    public function invokeTextSuccessProvider(): array
    {
        return [
            'Name ascending'  => [[0, 1], ['--name' => true, '--asc' => true]],
            'Name descending' => [[1, 0], ['--name' => true, '--desc' => true]],
            'Time ascending'  => [[1, 0], ['--time' => true, '--asc' => true]],
            'Time descending' => [[0, 1], ['--time' => true, '--desc' => true]],
        ];
    }

    public function testInvokeJsonSuccess(): void
    {
        $table = $this->setSuccessMockHandler();
        unset($table['TemplatesMetadata'][0]['No'], $table['TemplatesMetadata'][1]['No']);

        $this->artisan('ses-template:list-templates', ['--json' => true])
            ->expectsOutput(json_encode($table))
            ->assertSuccessful();
    }

    public function testInvokeTextFailure(): void
    {
        $this->setFailureMockHandler();

        $this->artisan('ses-template:list-templates', [])
            ->assertFailed();
    }

    public function testInvokeJsonFailure(): void
    {
        $this->setFailureMockHandler();

        $this->artisan('ses-template:list-templates', ['--json' => true])
            ->expectsOutput('null')
            ->assertFailed();
    }
}
