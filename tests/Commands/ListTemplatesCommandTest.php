<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Commands;

use Aws\Api\DateTimeResult;
use Aws\MockHandler;
use Aws\Result;
use Exception;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class ListTemplatesCommandTest extends TestCase
{
    protected function setSuccessMockHandler(array $templatesMetadata, array $template): void
    {
        $mockHandler = new MockHandler();
        foreach ($templatesMetadata as $metadata) {
            $mockHandler->append(new Result($metadata));
        }
        $mockHandler->append(new Result($template));

        config(['services.ses.handler' => $mockHandler]);
    }

    protected function setFailureMockHandler(): void
    {
        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['TemplatesMetadata' => [], 'NextToken' => null]));

        config(['services.ses.handler' => $mockHandler]);
    }

    /**
     * @dataProvider invokeTextSuccessProvider
     *
     * @param  int[]  $nums
     *
     * @throws Exception
     */
    #[DefineEnvironment('usesSesV1Transport')]
    public function testSesV1InvokeTextSuccess(array $nums, array $options): void
    {
        $templatesMetadata = [
            [
                'TemplatesMetadata' => [
                    [
                        'Name' => 'NewsAndUpdates',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2021-10-03T20:03:34.574Z'),
                    ],
                    [
                        'Name' => 'YourFavorite',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2022-01-13T14:45:54.636Z'),
                    ],
                ],
                'NextToken' => 'token',
            ],
            [
                'TemplatesMetadata' => [
                    [
                        'Name' => 'SpecialOffers',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2020-08-05T16:04:12.640Z'),
                    ],
                ],
            ],
        ];

        $template = [
            'Template' => [
                'TemplateName' => 'MyTemplate',
                'SubjectPart' => 'Greetings, {{name}}!',
                'HtmlPart' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
                'TextPart' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
            ],
        ];

        $this->setSuccessMockHandler($templatesMetadata, $template);

        $table = [];
        $count = 0;
        foreach ($templatesMetadata as $metadata) {
            foreach ($metadata['TemplatesMetadata'] as $data) {
                $data['No'] = $count;
                $table[] = [
                    'No' => $nums[$count],
                    'Name' => $data['Name'],
                    'CreatedTimestamp' => $data['CreatedTimestamp'],
                ];
                $count++;
            }
        }

        $this->artisan('ses-template:list-templates', $options)
            ->expectsTable(['No', 'Name', 'CreatedTimestamp'], collect($table)->sortBy('No')->values()->all())
            ->expectsQuestion('Enter a number to display the template object', '0')
            ->assertSuccessful();
    }

    /**
     * @dataProvider invokeTextSuccessProvider
     *
     * @param  int[]  $nums
     *
     * @throws Exception
     */
    #[DefineEnvironment('usesSesV2Transport')]
    public function testSesV2InvokeTextSuccess(array $nums, array $options): void
    {
        $templatesMetadata = [
            [
                'TemplatesMetadata' => [
                    [
                        'TemplateName' => 'NewsAndUpdates',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2021-10-03T20:03:34.574Z'),
                    ],
                    [
                        'TemplateName' => 'YourFavorite',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2022-01-13T14:45:54.636Z'),
                    ],
                ],
                'NextToken' => 'token',
            ],
            [
                'TemplatesMetadata' => [
                    [
                        'TemplateName' => 'SpecialOffers',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2020-08-05T16:04:12.640Z'),
                    ],
                ],
            ],
        ];

        $template = [
            'TemplateName' => 'MyTemplate',
            'TemplateContent' => [
                'Subject' => 'Greetings, {{name}}!',
                'Text' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
                'Html' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
            ],
        ];

        $this->setSuccessMockHandler($templatesMetadata, $template);

        $table = [];
        $count = 0;
        foreach ($templatesMetadata as $metadata) {
            foreach ($metadata['TemplatesMetadata'] as $data) {
                $data['No'] = $count;
                $table[] = [
                    'No' => $nums[$count],
                    'Name' => $data['TemplateName'],
                    'CreatedTimestamp' => $data['CreatedTimestamp'],
                ];
                $count++;
            }
        }

        $this->artisan('ses-template:list-templates', $options)
            ->expectsTable(['No', 'Name', 'CreatedTimestamp'], collect($table)->sortBy('No')->values()->all())
            ->expectsQuestion('Enter a number to display the template object', '0')
            ->assertSuccessful();
    }

    public static function invokeTextSuccessProvider(): array
    {
        return [
            'Name ascending' => [[0, 2, 1], ['--name' => true, '--asc' => true]],
            'Name descending' => [[2, 0, 1], ['--name' => true, '--desc' => true]],
            'Time ascending' => [[1, 2, 0], ['--time' => true, '--asc' => true]],
            'Time descending' => [[1, 0, 2], ['--time' => true, '--desc' => true]],
        ];
    }

    /**
     * @throws Exception
     */
    #[DefineEnvironment('usesSesV1Transport')]
    public function testSesV1InvokeJsonSuccess(): void
    {
        $templatesMetadata = [
            [
                'TemplatesMetadata' => [
                    [
                        'Name' => 'NewsAndUpdates',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2021-10-03T20:03:34.574Z'),
                    ],
                    [
                        'Name' => 'YourFavorite',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2022-01-13T14:45:54.636Z'),
                    ],
                ],
                'NextToken' => 'token',
            ],
            [
                'TemplatesMetadata' => [
                    [
                        'Name' => 'SpecialOffers',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2020-08-05T16:04:12.640Z'),
                    ],
                ],
            ],
        ];

        $template = [
            'Template' => [
                'TemplateName' => 'MyTemplate',
                'SubjectPart' => 'Greetings, {{name}}!',
                'HtmlPart' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
                'TextPart' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
            ],
        ];

        $this->setSuccessMockHandler($templatesMetadata, $template);

        $table = [];
        foreach ($templatesMetadata as $metadata) {
            foreach ($metadata['TemplatesMetadata'] as $data) {
                $table[] = $data;
            }
        }

        $this->artisan('ses-template:list-templates', ['--json' => true])
            ->expectsOutput(json_encode(['TemplatesMetadata' => collect($table)->sortBy('Name')->values()->all()]))
            ->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    #[DefineEnvironment('usesSesV2Transport')]
    public function testSesV2InvokeJsonSuccess(): void
    {
        $templatesMetadata = [
            [
                'TemplatesMetadata' => [
                    [
                        'TemplateName' => 'NewsAndUpdates',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2021-10-03T20:03:34.574Z'),
                    ],
                    [
                        'TemplateName' => 'YourFavorite',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2022-01-13T14:45:54.636Z'),
                    ],
                ],
                'NextToken' => 'token',
            ],
            [
                'TemplatesMetadata' => [
                    [
                        'TemplateName' => 'SpecialOffers',
                        'CreatedTimestamp' => DateTimeResult::fromTimestamp('2020-08-05T16:04:12.640Z'),
                    ],
                ],
            ],
        ];

        $template = [
            'TemplateName' => 'MyTemplate',
            'TemplateContent' => [
                'Subject' => 'Greetings, {{name}}!',
                'Text' => "Dear {{name}},\r\nYour favorite animal is {{favoriteanimal}}.",
                'Html' => '<h1>Hello {{name}},</h1><p>Your favorite animal is {{favoriteanimal}}.</p>',
            ],
        ];

        $this->setSuccessMockHandler($templatesMetadata, $template);

        $table = [];
        foreach ($templatesMetadata as $metadata) {
            foreach ($metadata['TemplatesMetadata'] as $data) {
                $table[] = $data;
            }
        }

        $this->artisan('ses-template:list-templates', ['--json' => true])
            ->expectsOutput(json_encode(['TemplatesMetadata' => collect($table)->sortBy('TemplateName')->values()->all()]))
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
