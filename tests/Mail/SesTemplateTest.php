<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Mail;

use Illuminate\Mail\Mailables\Address;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplateOptions;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class SesTemplateTest extends TestCase
{
    /**
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[DefineEnvironment('usesSesV1Transport')]
    public function testSesV1Render(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->build();

        self::assertSame('TestTemplate', $mailable->subject);

        self::assertSame(json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR), $mailable->render());
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[DefineEnvironment('usesSesV2Transport')]
    public function testSesV2Render(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->build();

        self::assertSame('TestTemplate', $mailable->subject);

        self::assertSame(json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR), $mailable->render());
    }

    /**
     * @throws \JsonException
     */
    public function testBuildWithFrom(): void
    {
        $options = new SesTemplateOptions;
        $options->from(new Address('example@example.com', 'example name'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => $options->from?->name, 'address' => $options->from?->address]], $mailable->from);
    }

    /**
     * @throws \JsonException
     */
    public function testBuildWithFromOnlyAddress(): void
    {
        $options = new SesTemplateOptions;
        $options->from(new Address('example@example.com'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => null, 'address' => $options->from?->address]], $mailable->from);
    }

    /**
     * @throws \JsonException
     */
    public function testBuildWithReplyTo(): void
    {
        $options = new SesTemplateOptions;
        $options->replyTo(new Address('example@example.com', 'example name'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => $options->replyTo?->name, 'address' => $options->replyTo?->address]], $mailable->replyTo);
    }

    /**
     * @throws \JsonException
     */
    public function testBuildWithReplyToOnlyAddress(): void
    {
        $options = new SesTemplateOptions;
        $options->replyTo(new Address('example@example.com'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => null, 'address' => $options->replyTo?->address]], $mailable->replyTo);
    }

    /**
     * @throws \JsonException
     */
    #[DefineEnvironment('usesSesV2Transport')]
    public function testBuildWithHeaders(): void
    {
        $options = new SesTemplateOptions;
        $options->header('X-Foo', 'foo')
            ->header('X-Bar', 'bar');

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $mailable->assertHasMetadata('X-Foo', 'foo');
        $mailable->assertHasMetadata('X-Bar', 'bar');
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
        $options = new SesTemplateOptions;
        $options->from(new Address('example@example.com', 'example name'))
            ->replyTo(new Address('example@example.com', 'example name'))
            ->header('X-Foo', 'foo')
            ->header('X-Bar', 'bar');

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->setOptions($options);

        self::assertSame($options, $mailable->getOptions());
    }
}
