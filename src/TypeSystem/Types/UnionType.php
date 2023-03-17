<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\Collection;
use Tochka\TypeParser\Exceptions\LogicException;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @template-covariant TType
 * @implements TypeInterface<TType>
 */
final class UnionType implements TypeInterface
{
    /** @var Collection<TypeInterface<TType>> */
    public readonly Collection $types;

    /**
     * @param non-empty-list<TypeInterface<TType>> $types
     */
    public function __construct(array $types)
    {
        if (count($types) < 2) {
            throw new LogicException('Intersection type must contain at least 2 types');
        }

        $this->types = new Collection($types);
    }

    public function __toString(): string
    {
        return implode('|', $this->types->all());
    }
}
