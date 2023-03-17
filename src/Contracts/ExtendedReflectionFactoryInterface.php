<?php

namespace Tochka\TypeParser\Contracts;

use Tochka\TypeParser\Reflectors\ExtendedClassReflection;
use Tochka\TypeParser\Reflectors\ExtendedMethodReflection;
use Tochka\TypeParser\Reflectors\ExtendedParameterReflection;
use Tochka\TypeParser\Reflectors\ExtendedPropertyReflection;

/**
 * @psalm-api
 */
interface ExtendedReflectionFactoryInterface
{
    /**
     * @param class-string $className
     * @throws \ReflectionException
     */
    public function makeForClass(string $className): ExtendedClassReflection;

    /**
     * @param class-string $className
     * @throws \ReflectionException
     */
    public function makeForMethod(string $className, string $methodName): ExtendedMethodReflection;

    /**
     * @param class-string $className
     * @throws \ReflectionException
     */
    public function makeForProperty(string $className, string $propertyName): ExtendedPropertyReflection;

    /**
     * @param class-string $className
     * @throws \ReflectionException
     */
    public function makeForParameter(
        string $className,
        string $methodName,
        string $parameterName
    ): ExtendedParameterReflection;
}
