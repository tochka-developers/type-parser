<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @implements TypeInterface<int>
 */
final class IntType implements TypeInterface
{
    public function __construct(
        public readonly ?int $min = null,
        public readonly ?int $max = null
    ) {
    }

    public function __toString(): string
    {
        return 'int';
    }
}
