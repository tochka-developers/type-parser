<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @implements TypeInterface<never>
 */
final class NeverType implements TypeInterface
{
    public function __toString(): string
    {
        return 'never';
    }
}
