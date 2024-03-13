<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Commands;

use Aws\Ses\SesClient;
use Illuminate\Console\Command as BaseCommand;
use JsonException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand
{
    protected bool $isJson;

    /**
     * Create a new command instance.
     */
    public function __construct(protected SesClient $ses)
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

    public function error($string, $verbosity = null): void
    {
        if ($this->isJson) {
            $this->json(null);
        } else {
            parent::error($string, $verbosity);
        }
    }
}
