<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @implements TypeInterface<void>
 */
final class VoidType implements TypeInterface
{
    public function __toString(): string
    {
        return 'void';
    }
}
