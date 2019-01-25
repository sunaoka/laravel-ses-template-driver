<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @param mixed  $class
     * @param string $name
     *
     * @return \ReflectionProperty
     * @throws \ReflectionException
     */
    protected function getRestrictedProperty($class, $name)
    {
        $reflectionClass = new \ReflectionClass($class);

        $property = $reflectionClass->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($class);
    }

    /**
     * @param  mixed  $class
     * @param  string $name
     * @param  array  $args
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function callRestrictedMethod($class, $name, array $args = [])
    {
        $reflectionMethod = new \ReflectionMethod($class, $name);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($class, $args);
    }
}
