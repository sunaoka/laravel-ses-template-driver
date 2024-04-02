<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Commands;

use Aws\Exception\AwsException;

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
     * @throws \JsonException
     */
    public function handle(): int
    {
        /** @var string $templateName */
        $templateName = $this->argument('TemplateName');

        try {
            $template = $this->sesService->getTemplate($templateName);
        } catch (AwsException $e) {
            $this->error((string) $e->getAwsErrorMessage());

            return Command::FAILURE;
        }

        if ($this->isJson) {
            $this->json($template);

            return Command::SUCCESS;
        }

        $this->print($template);

        return Command::SUCCESS;
    }
}
