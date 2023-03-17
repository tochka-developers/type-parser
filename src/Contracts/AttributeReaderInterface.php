<?php

namespace Tochka\TypeParser\Contracts;

use Tochka\TypeParser\Collection;

/**
 * @psalm-api
 */
interface AttributeReaderInterface
{
    /**
     * Gets a list of attributes and/or annotations applied to a class
     *
     * @template T of object
     *
     * @param \ReflectionClass $class The reflection instance of the class from which the class annotations should be read
     * @return Collection<T> A list of class annotations and/or attributes
     */
    public function getClassMetadata(\ReflectionClass $class): Collection;

    /**
     * Gets a list of attributes and/or annotations applied to a function or method
     *
     * @template T of object
     *
     * @param \ReflectionFunctionAbstract $function The reflection instance of the function or method from which the
     * function annotations should be read
     * @return Collection<T> A list of function annotations and/or attributes
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function): Collection;

    /**
     * Gets a list of attributes and/or annotations applied to a class property.
     *
     * @template T of object
     *
     * @param \ReflectionProperty $property The reflection instance of the property from which the property annotations should be read.
     * @return Collection<T> A list of property annotations and/or attributes.
     */
    public function getPropertyMetadata(\ReflectionProperty $property): Collection;


    /**
     * Gets a list of attributes and/or annotations applied to a parameter of a function or method.
     *
     * @template T of object
     *
     * @param \ReflectionParameter $parameter The reflection instance of the parameter from which the parameter annotations should be read.
     * @return Collection<T> A list of parameter annotations and/or attributes.
     */
    public function getParameterMetadata(\ReflectionParameter $parameter): Collection;

}
