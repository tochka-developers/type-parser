<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @implements TypeInterface<object>
 */
final class ObjectType implements TypeInterface
{
    public function __toString(): string
    {
        return 'object';
    }
}
