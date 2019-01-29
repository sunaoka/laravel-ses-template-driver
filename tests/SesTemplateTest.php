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
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], [
            'from' => [
                'address' => 'example@example.com',
                'name'    => 'example name',
            ]]);
        $mailable->build();

        $this->assertEquals([['address' => 'example@example.com', 'name' => 'example name']], $mailable->from);

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], [
            'from' => [
                'xxxxxxx' => 'example@example.com',
                'name'    => 'example name',
            ]]);
        $mailable->build();

        $this->assertEquals([], $mailable->from);
    }

    public function testBuildWithFromOnlyAddress()
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], ['from' => ['address' => 'example@example.com']]);
        $mailable->build();

        $this->assertEquals([['address' => 'example@example.com', 'name' => null]], $mailable->from);

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], ['from' => ['xxxxxxx' => 'example@example.com']]);
        $mailable->build();

        $this->assertEquals([], $mailable->from);
    }

    public function testBuildWithReplyTo()
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], [
            'reply_to' => [
                'address' => 'example@example.com',
                'name'    => 'example name',
            ]]);
        $mailable->build();

        $this->assertEquals([['address' => 'example@example.com', 'name' => 'example name']], $mailable->replyTo);

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], [
            'reply_to' => [
                'xxxxxxx' => 'example@example.com',
                'name'    => 'example name',
            ]]);
        $mailable->build();

        $this->assertEquals([], $mailable->replyTo);
    }

    public function testBuildWithReplyToOnlyAddress()
    {
        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], ['reply_to' => ['address' => 'example@example.com']]);
        $mailable->build();

        $this->assertEquals([['address' => 'example@example.com', 'name' => null]], $mailable->replyTo);

        $mailable = new SesTemplate('TestTemplate', ['foo' => 'bar'], ['reply_to' => ['xxxxxxx' => 'example@example
        .com']]);
        $mailable->build();

        $this->assertEquals([], $mailable->replyTo);
    }
}
