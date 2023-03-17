<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeAliasInterface;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @implements TypeAliasInterface<scalar>
 */
final class ScalarType implements TypeAliasInterface
{
    public function type(): TypeInterface
    {
        return new UnionType(
            [
                new BoolType(),
                new IntType(),
                new FloatType(),
                new StringType()
            ]
        );
    }

    public function __toString(): string
    {
        return 'scalar';
    }
}
