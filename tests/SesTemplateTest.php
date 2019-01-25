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
}
