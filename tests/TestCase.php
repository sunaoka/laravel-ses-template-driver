<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @param $className
     * @param $name
     *
     * @return \ReflectionProperty
     * @throws \ReflectionException
     */
    protected function getProperty($className, $name)
    {
        $class = new \ReflectionClass($className);

        $property = $class->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($className);
    }
}
