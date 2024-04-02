<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Commands;

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
     * @throws \JsonException
     * @throws \Exception
     */
    public function handle(): int
    {
        $templates = $this->sesService->listTemplates();
        if ($templates->isEmpty()) {
            $this->error('No templates found.');

            return Command::FAILURE;
        }

        $structure = $this->sesService->getListStructure();

        $descending = (bool) $this->option('desc');
        $sort = $this->option('time') ? $structure['CreatedTimestamp'] : $structure['TemplateName'];

        $templates = $templates->sortBy($sort, SORT_NATURAL, $descending)->values();

        if ($this->isJson) {
            $this->json(['TemplatesMetadata' => $templates]);

            return Command::SUCCESS;
        }

        $timezone = new \DateTimeZone(config('app.timezone'));  // @phpstan-ignore-line
        $choices = [];
        foreach ($templates as $index => $template) {
            $choices[] = [
                'No' => $index,
                'Name' => $template[$structure['TemplateName']],
                'CreatedTimestamp' => $template[$structure['CreatedTimestamp']]->setTimezone($timezone),
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
}
