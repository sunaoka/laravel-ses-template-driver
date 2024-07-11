<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Services;

use Aws\SesV2\SesV2Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @phpstan-import-type EmailTemplateMetadata from SesServiceInterface
 * @phpstan-import-type EmailTemplate from SesServiceInterface
 */
class SesV2Service implements SesServiceInterface
{
    public function __construct(protected SesV2Client $client) {}

    public function getClient(): SesV2Client
    {
        return $this->client;
    }

    public function listTemplates(): Collection
    {
        $templates = new Collection();

        $results = $this->getClient()->getPaginator('ListEmailTemplates', [
            'PageSize' => 100,
        ]);

        foreach ($results->search('TemplatesMetadata') as $result) {
            /** @var EmailTemplateMetadata $result */
            $templates->add($result);
        }

        return $templates;
    }

    /**
     * @return EmailTemplate
     */
    public function getTemplate(string $templateName): array
    {
        $template = $this->getClient()->getEmailTemplate([
            'TemplateName' => $templateName,
        ]);

        /** @var EmailTemplate */
        return Arr::except($template->toArray(), '@metadata');
    }

    public function getListStructure(): array
    {
        return [
            'TemplateName' => 'TemplateName',
            'CreatedTimestamp' => 'CreatedTimestamp',
        ];
    }
}
