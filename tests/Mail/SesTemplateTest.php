<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Mail;

use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class SesTemplateTest extends TestCase
{
    public function testBuild(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->build();

        self::assertSame('TestTemplate', $mailable->subject);

        self::assertSame(json_encode(['foo' => 'bar']), $mailable->render());
    }

    public function testBuildWithFrom(): void
    {
        $options = [
            'from' => [
                'address' => 'example@example.com',
                'name' => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertEqualsCanonicalizing([$options['from']], $mailable->from);

        $options = [
            'from' => [
                'xxxxxxx' => 'example@example.com',
                'name' => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([], $mailable->from);
    }

    public function testBuildWithFromOnlyAddress(): void
    {
        $options = [
            'from' => [
                'address' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertEqualsCanonicalizing([$options['from'] + ['name' => null]], $mailable->from);

        $options = [
            'from' => [
                'xxxxxxx' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([], $mailable->from);
    }

    public function testBuildWithReplyTo(): void
    {
        $options = [
            'reply_to' => [
                'address' => 'example@example.com',
                'name' => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertEqualsCanonicalizing([$options['reply_to']], $mailable->replyTo);

        $options = [
            'reply_to' => [
                'xxxxxxx' => 'example@example.com',
                'name' => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([], $mailable->replyTo);
    }

    public function testBuildWithReplyToOnlyAddress(): void
    {
        $options = [
            'reply_to' => [
                'address' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertEqualsCanonicalizing([$options['reply_to'] + ['name' => null]], $mailable->replyTo);

        $options = [
            'reply_to' => [
                'xxxxxxx' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([], $mailable->replyTo);
    }

    public function testTemplateName(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        self::assertSame('TestTemplate', $mailable->getTemplateName());

        $mailable->setTemplateName('ModifiedTemplate');

        self::assertSame('ModifiedTemplate', $mailable->getTemplateName());
    }

    public function testTemplateData(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        self::assertSame(['foo' => 'bar'], $mailable->getTemplateData());

        $mailable->setTemplateData(['baz' => 'qux']);

        self::assertSame(['baz' => 'qux'], $mailable->getTemplateData());
    }

    public function testOptions(): void
    {
        $options = [
            'from' => [
                'address' => 'example@example.com',
                'name' => 'example name',
            ],
            'reply_to' => [
                'address' => 'example@example.com',
                'name' => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->setOptions($options);

        self::assertSame($options, $mailable->getOptions());
    }
}
