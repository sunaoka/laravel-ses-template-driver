<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Commands;

use Aws\Api\DateTimeResult;
use DateTimeZone;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use JsonException;

/**
 * @phpstan-type TemplateMetadata array{Name: string, CreatedTimestamp: \Aws\Api\DateTimeResult}
 */
class ListTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'EOF'
    ses-template:list-templates 
        {--name : Sort by the name of the template <comment>[default]</comment>}
        {--time : Sort by the time and date the template was created}
        {--asc  : Sort by ascending order <comment>[default]</comment>}
        {--desc : Sort by descending order}
        {--json : The output is formatted as a JSON string}
    EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the email templates present in your Amazon SES account in the current AWS Region';

    /**
     * Execute the console command.
     *
     * @throws JsonException
     * @throws Exception
     */
    public function handle(): int
    {
        $templates = $this->listTemplates();
        if ($templates->isEmpty()) {
            $this->error('No templates found.');

            return Command::FAILURE;
        }

        $descending = (bool) $this->option('desc');
        $sort = $this->option('time') ? 'CreatedTimestamp' : 'Name';

        $templates = $templates->sortBy($sort, SORT_NATURAL, $descending)->values();

        if ($this->isJson) {
            $this->json(['TemplatesMetadata' => $templates]);

            return Command::SUCCESS;
        }

        $timezone = new DateTimeZone(Config::get('app.timezone', 'UTC'));  // @phpstan-ignore-line
        $choices = [];
        foreach ($templates as $index => $template) {
            /** @var array{Name: string, CreatedTimestamp: DateTimeResult} $template */
            $choices[] = [
                'No' => $index,
                'Name' => $template['Name'],
                'CreatedTimestamp' => $template['CreatedTimestamp']->setTimezone($timezone),
            ];
        }

        $this->table(
            ['No', 'Name', 'CreatedTimestamp'],
            $choices
        );

        $answer = $this->ask('Enter a number to display the template object');
        if (isset($choices[$answer])) {
            $this->call('ses-template:get-template', [
                'TemplateName' => $choices[$answer]['Name'],
            ]);
        }

        return Command::SUCCESS;
    }

    /**
     * @param  Collection<int, TemplateMetadata>|null  $templates
     * @return Collection<int, TemplateMetadata>
     */
    private function listTemplates(?string $nextToken = null, ?Collection $templates = null): Collection
    {
        if ($templates === null) {
            $templates = new Collection();
        }
        $this->output->write('.');

        $start = microtime(true);

        /** @var array{TemplatesMetadata: TemplateMetadata[], NextToken: string|null} $result */
        $result = $this->ses->listTemplates([
            'MaxItems' => 100,
            'NextToken' => $nextToken,
        ]);

        $template = $result['TemplatesMetadata'];
        if (count($template) > 0) {
            $templates = $templates->merge($template);
        }

        if ($result['NextToken'] !== null) {
            // You can execute this operation no more than once per second.
            // @see <https://docs.aws.amazon.com/ses/latest/APIReference/API_ListTemplates.html>
            $wait = (int) ((1 - (microtime(true) - $start)) * 1000000);
            if ($wait > 0) {
                usleep($wait);
            }

            return $this->listTemplates($result['NextToken'], $templates);
        }

        $this->info(' done.');

        return $templates;
    }
}
