<?php

namespace Sunaoka\LaravelSesTemplateDriver\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class TestCase extends BaseTestCase
{
    /**
     * @param mixed  $class
     * @param string $name
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function getRestrictedProperty(mixed $class, string $name): mixed
    {
        $reflectionClass = new ReflectionClass($class);

        $property = $reflectionClass->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($class);
    }

    /**
     * @param mixed  $class
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function callRestrictedMethod(mixed $class, string $name, array $args = []): mixed
    {
        $reflectionMethod = new ReflectionMethod($class, $name);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($class, $args);
    }
}
