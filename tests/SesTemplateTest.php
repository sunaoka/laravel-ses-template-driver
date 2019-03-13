<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use Sunaoka\LaravelSesTemplateDriver\Mail\SesTemplate;

class SesTemplateTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testBuild()
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->build();

        $this->assertEquals('TestTemplate', $mailable->subject);

        $html = $this->getRestrictedProperty($mailable, 'html');

        $this->assertEquals(json_encode(['foo' => 'bar']), $html);
    }

    public function testBuildWithFrom()
    {
        $options = [
            'from' => [
                'address' => 'example@example.com',
                'name'    => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([$options['from']], $mailable->from);

        $options = [
            'from' => [
                'xxxxxxx' => 'example@example.com',
                'name'    => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([], $mailable->from);
    }

    public function testBuildWithFromOnlyAddress()
    {
        $options = [
            'from' => [
                'address' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([$options['from'] + ['name' => null]], $mailable->from);

        $options = [
            'from' => [
                'xxxxxxx' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([], $mailable->from);
    }

    public function testBuildWithReplyTo()
    {
        $options = [
            'reply_to' => [
                'address' => 'example@example.com',
                'name'    => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([$options['reply_to']], $mailable->replyTo);

        $options = [
            'reply_to' => [
                'xxxxxxx' => 'example@example.com',
                'name'    => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([], $mailable->replyTo);
    }

    public function testBuildWithReplyToOnlyAddress()
    {
        $options = [
            'reply_to' => [
                'address' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([$options['reply_to'] + ['name' => null]], $mailable->replyTo);

        $options = [
            'reply_to' => [
                'xxxxxxx' => 'example@example.com',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], $options);
        $mailable->build();

        $this->assertEquals([], $mailable->replyTo);
    }

    public function testTemplateName()
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $this->assertEquals('TestTemplate', $mailable->getTemplateName());

        $mailable->setTemplateName('ModifiedTemplate');

        $this->assertEquals('ModifiedTemplate', $mailable->getTemplateName());
    }

    public function testTemplateData()
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $mailable->getTemplateData());

        $mailable->setTemplateData(['baz' => 'qux']);

        $this->assertSame(['baz' => 'qux'], $mailable->getTemplateData());
    }

    public function testOptions()
    {
        $options = [
            'from' => [
                'address' => 'example@example.com',
                'name'    => 'example name',
            ],
            'reply_to' => [
                'address' => 'example@example.com',
                'name'    => 'example name',
            ],
        ];
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar']);
        $mailable->setOptions($options);

        $this->assertEquals($options, $mailable->getOptions());
    }
}
