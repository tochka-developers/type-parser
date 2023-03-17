<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\DTO\StringRestrictionEnum;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @implements TypeInterface<string>
 */
final class StringType implements TypeInterface
{
    public function __construct(
        public readonly StringRestrictionEnum $restriction = StringRestrictionEnum::NONE
    ) {
    }

    public function __toString(): string
    {
        return 'string';
    }
}
