<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Commands;

use Aws\Exception\AwsException;
use JsonException;

class GetTemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'EOF'
    ses-template:get-template 
        {TemplateName : The name of the template to retrieve}
        {--json : The output is formatted as a JSON string}
    EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays the template object for the template you specify';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws JsonException
     */
    public function handle(): int
    {
        $templateName = $this->argument('TemplateName');

        try {
            $template = $this->getTemplate($templateName);
        } catch (AwsException $e) {
            $this->error($e->getAwsErrorMessage());
            return Command::FAILURE;
        }

        if ($this->isJson) {
            $this->json(['Template' => $template]);
            return Command::SUCCESS;
        }

        foreach ($template as $key => $value) {
            $this->info("{$key}:");
            $this->line($value);
            $this->newLine();
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $templateName
     *
     * @return string[]
     *
     * @throws AwsException
     */
    private function getTemplate(string $templateName): array
    {
        $template = $this->ses->getTemplate([
            'TemplateName' => $templateName,
        ]);

        return $template['Template'];
    }
}
