<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Contracts;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 */
interface ExtendedValueReflectionInterface extends ExtendedReflectionInterface
{
    public function getType(): TypeInterface;

    public function hasDefaultValue(): bool;

    public function isRequired(): bool;

    public function getDefaultValue(): mixed;
}
