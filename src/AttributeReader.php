<?php

declare(strict_types=1);

namespace Tochka\TypeParser;

use Spiral\Attributes\ReaderInterface;
use Tochka\TypeParser\Contracts\AttributeReaderInterface;

/**
 * @psalm-api
 */
class AttributeReader implements AttributeReaderInterface
{
    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    public function getClassMetadata(\ReflectionClass $class): Collection
    {
        return $this->makeCollectionFromIterable($this->reader->getClassMetadata($class));
    }

    public function getFunctionMetadata(\ReflectionFunctionAbstract $function): Collection
    {
        return $this->makeCollectionFromIterable($this->reader->getFunctionMetadata($function));
    }

    public function getPropertyMetadata(\ReflectionProperty $property): Collection
    {
        return $this->makeCollectionFromIterable($this->reader->getPropertyMetadata($property));
    }

    public function getParameterMetadata(\ReflectionParameter $parameter): Collection
    {
        return $this->makeCollectionFromIterable($this->reader->getParameterMetadata($parameter));
    }

    /**
     * @template TItem
     * @param iterable<TItem> $items
     * @return Collection<TItem>
     */
    private function makeCollectionFromIterable(iterable $items): Collection
    {
        return new Collection($items instanceof \Traversable ? iterator_to_array($items) : $items);
    }
}
