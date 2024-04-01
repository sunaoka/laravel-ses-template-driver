<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Mail;

use Illuminate\Mail\Mailables\Address;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use ReflectionException;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplateOptions;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class SesTemplateTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    #[DefineEnvironment('usesSesV1Transport')]
    public function testSesV1Render(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->build();

        self::assertSame('TestTemplate', $mailable->subject);

        self::assertSame(json_encode(['foo' => 'bar']), $mailable->render());
    }

    /**
     * @throws ReflectionException
     */
    #[DefineEnvironment('usesSesV2Transport')]
    public function testSesV2Render(): void
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->build();

        self::assertSame('TestTemplate', $mailable->subject);

        self::assertSame(json_encode(['foo' => 'bar']), $mailable->render());
    }

    public function testBuildWithFrom(): void
    {
        $options = new SesTemplateOptions();
        $options->from(new Address('example@example.com', 'example name'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => $options->from?->name, 'address' => $options->from?->address]], $mailable->from);
    }

    public function testBuildWithFromOnlyAddress(): void
    {
        $options = new SesTemplateOptions();
        $options->from(new Address('example@example.com'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => null, 'address' => $options->from?->address]], $mailable->from);
    }

    public function testBuildWithReplyTo(): void
    {
        $options = new SesTemplateOptions();
        $options->replyTo(new Address('example@example.com', 'example name'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => $options->replyTo?->name, 'address' => $options->replyTo?->address]], $mailable->replyTo);
    }

    public function testBuildWithReplyToOnlyAddress(): void
    {
        $options = new SesTemplateOptions();
        $options->replyTo(new Address('example@example.com'));

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        self::assertSame([['name' => null, 'address' => $options->replyTo?->address]], $mailable->replyTo);
    }

    #[DefineEnvironment('usesSesV2Transport')]
    public function testBuildWithHeaders(): void
    {
        $options = new SesTemplateOptions();
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
        $options = new SesTemplateOptions();
        $options->from(new Address('example@example.com', 'example name'))
            ->replyTo(new Address('example@example.com', 'example name'))
            ->header('X-Foo', 'foo')
            ->header('X-Bar', 'bar');

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->setOptions($options);

        self::assertSame($options, $mailable->getOptions());
    }
}
