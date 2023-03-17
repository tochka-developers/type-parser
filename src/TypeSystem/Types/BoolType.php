<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\DTO\BoolRestrictionEnum;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @implements TypeInterface<bool>
 */
final class BoolType implements TypeInterface
{
    public function __construct(
        public readonly BoolRestrictionEnum $restriction = BoolRestrictionEnum::NONE
    ) {
    }

    public function __toString(): string
    {
        return 'bool';
    }
}
