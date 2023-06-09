<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @template-covariant TKey
 * @template-covariant TValue
 * @implements TypeInterface<iterable<TKey, TValue>>
 */
final class IterableType implements TypeInterface
{
    /**
     * @param TypeInterface<TKey> $keyType
     * @param TypeInterface<TValue> $valueType
     */
    public function __construct(
        public readonly TypeInterface $keyType = new MixedType(),
        public readonly TypeInterface $valueType = new MixedType()
    ) {
    }

    public function __toString(): string
    {
        return sprintf('iterable<%s,%s>', (string) $this->keyType, (string) $this->valueType);
    }
}
