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

        $html = $this->getProperty($mailable, 'html');

        $this->assertEquals(json_encode(['foo' => 'bar']), $html);
    }
}
