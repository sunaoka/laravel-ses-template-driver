<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Commands;

use Illuminate\Console\Command as BaseCommand;
use JsonException;
use Sunaoka\LaravelSesTemplateDriver\Services\SesServiceInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand
{
    protected bool $isJson;

    /**
     * Create a new command instance.
     */
    public function __construct(protected SesServiceInterface $sesService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->isJson = (bool) $this->option('json');
        if ($this->isJson) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        return parent::execute($input, $output);
    }

    /**
     * Writes a json to the output
     *
     * @throws JsonException
     */
    protected function json(mixed $value): void
    {
        $this->getOutput()->writeln(json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), OutputInterface::VERBOSITY_QUIET);
    }

    protected function print(array $array): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->print($value);
            } else {
                $this->info("{$key}:");
                $this->line($value);
                $this->newLine();
            }
        }
    }

    public function error($string, $verbosity = null): void
    {
        if ($this->isJson) {
            $this->json(null);
        } else {
            parent::error($string, $verbosity);
        }
    }
}
