<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Services;

use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use Aws\SesV2\SesV2Client;
use Illuminate\Support\Collection;

/**
 * @phpstan-type TemplateMetadata array{Name: string, CreatedTimestamp: \Aws\Api\DateTimeResult}
 * @phpstan-type Template array{Template: array{TemplateName: string, SubjectPart: string, HtmlPart: string, TextPart: string}}
 * @phpstan-type EmailTemplateMetadata array{TemplateName: string, CreatedTimestamp: \Aws\Api\DateTimeResult}
 * @phpstan-type EmailTemplate array{TemplateName: string, TemplateContent: EmailTemplateContent}
 * @phpstan-type EmailTemplateContent array{Subject: string, Html: string, Text: string}
 */
interface SesServiceInterface
{
    public function getClient(): SesClient|SesV2Client;

    /**
     * @return Collection<int, TemplateMetadata|EmailTemplateMetadata>
     */
    public function listTemplates(): Collection;

    /**
     * @phpstan-return Template|EmailTemplate
     *
     * @throws AwsException
     */
    public function getTemplate(string $templateName): array;

    /**
     * @return array{TemplateName: 'Name'|'TemplateName', CreatedTimestamp: 'CreatedTimestamp'}
     */
    public function getListStructure(): array;
}
