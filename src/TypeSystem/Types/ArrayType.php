<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @template-covariant TKey of array-key
 * @template-covariant TValue
 * @implements TypeInterface<array<TKey, TValue>>
 */
final class ArrayType implements TypeInterface
{
    /**
     * @param TypeInterface<TKey> $keyType
     * @param TypeInterface<TValue> $valueType
     */
    public function __construct(
        public readonly TypeInterface $keyType = new ArrayKeyType(),
        public readonly TypeInterface $valueType = new MixedType()
    ) {
    }

    public function __toString(): string
    {
        return sprintf('array<%s,%s>', $this->keyType, $this->valueType);
    }
}
