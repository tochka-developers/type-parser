<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 *
 * @psalm-immutable
 * @template-covariant TReturn
 * @implements TypeInterface<callable(): TReturn>
 */
final class CallableType implements TypeInterface
{
    public function __toString(): string
    {
        return 'callable';
    }
}
