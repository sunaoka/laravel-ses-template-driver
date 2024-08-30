<?php

declare(strict_types=1);

namespace Sunaoka\LaravelSesTemplateDriver\Tests\Mail;

use Illuminate\Mail\Mailables\Address;
use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplateOptions;
use Sunaoka\LaravelSesTemplateDriver\Tests\TestCase;

class SesTemplateOptionsTest extends TestCase
{
    public function testCreate(): void
    {
        $options = new SesTemplateOptions(
            from: new Address('from@example.com', 'from'),
            replyTo: new Address('replyTo@example.com', 'replyTo'),
            headers: [
                'X-Foo' => 'foo',
                'X-Bar' => 'bar',
            ],
        );

        self::assertInstanceOf(Address::class, $options->from);
        self::assertSame('from@example.com', $options->from->address);
        self::assertSame('from', $options->from->name);

        self::assertInstanceOf(Address::class, $options->replyTo);
        self::assertSame('replyTo@example.com', $options->replyTo->address);
        self::assertSame('replyTo', $options->replyTo->name);

        self::assertIsArray($options->headers);
        self::assertSame('foo', $options->headers['X-Foo']);
        self::assertSame('bar', $options->headers['X-Bar']);
    }

    public function testCreateWithMethodChaining(): void
    {
        $options = new SesTemplateOptions;
        $options->from(new Address('from@example.com', 'from'))
            ->replyTo(new Address('replyTo@example.com', 'replyTo'))
            ->header('X-Foo', 'foo')
            ->header('X-Bar', 'bar');

        self::assertInstanceOf(Address::class, $options->from);
        self::assertSame('from@example.com', $options->from->address);
        self::assertSame('from', $options->from->name);

        self::assertInstanceOf(Address::class, $options->replyTo);
        self::assertSame('replyTo@example.com', $options->replyTo->address);
        self::assertSame('replyTo', $options->replyTo->name);

        self::assertIsArray($options->headers);
        self::assertSame('foo', $options->headers['X-Foo']);
        self::assertSame('bar', $options->headers['X-Bar']);
    }
}
