<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeAliasInterface;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @implements TypeAliasInterface<array-key>
 */
final class ArrayKeyType implements TypeAliasInterface
{
    public function type(): TypeInterface
    {
        return new UnionType(
            [
                new IntType(),
                new StringType(),
            ]
        );
    }

    public function __toString(): string
    {
        return 'array-key';
    }
}
