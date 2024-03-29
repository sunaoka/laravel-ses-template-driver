<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Services;

use Aws\Result;
use Aws\Ses\SesClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @phpstan-import-type Template from SesServiceInterface
 * @phpstan-import-type TemplateMetadata from SesServiceInterface
 */
class SesV1Service implements SesServiceInterface
{
    public function __construct(protected SesClient $client)
    {
    }

    public function getClient(): SesClient
    {
        return $this->client;
    }

    public function listTemplates(): Collection
    {
        $templates = new Collection();
        $nextToken = null;

        do {
            $start = microtime(true);

            /** @var Result|array{TemplatesMetadata: TemplateMetadata[], NextToken: string|null} $result */
            $result = $this->getClient()->listTemplates([
                'MaxItems' => 100,
                'NextToken' => $nextToken,
            ]);

            $template = $result['TemplatesMetadata'];
            if (count($template) > 0) {
                $templates = $templates->merge($template);
            }

            $nextToken = $result['NextToken'];
            if ($nextToken !== null) {
                $this->wait($start);
            }
        } while ($nextToken !== null);

        return $templates;
    }

    /**
     * All actions (except for SendEmail, SendRawEmail, and
     * SendTemplatedEmail) are throttled at one request per second.
     *
     * @link https://docs.aws.amazon.com/ses/latest/dg/quotas.html
     */
    private function wait(float $microseconds): void
    {
        $rate = 1.0;  // one request per second

        $wait = (int) (($rate - (microtime(true) - $microseconds)) * 1000000);
        if ($wait > 0) {
            usleep($wait);
        }
    }

    /**
     * @return Template
     */
    public function getTemplate(string $templateName): array
    {
        $template = $this->getClient()->getTemplate([
            'TemplateName' => $templateName,
        ]);

        /** @var Template */
        return Arr::except($template->toArray(), '@metadata');
    }

    public function getListStructure(): array
    {
        return [
            'TemplateName' => 'Name',
            'CreatedTimestamp' => 'CreatedTimestamp',
        ];
    }
}
