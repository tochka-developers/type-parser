<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\DTO;

use Tochka\TypeParser\TypeSystem\TypeInterface;
use Tochka\TypeParser\TypeSystem\Types\MixedType;

/**
 * @psalm-api
 * @psalm-immutable
 * @template-covariant TType
 */
final class Parameter
{
    public readonly TypeInterface $type;
    public readonly bool $default;
    public readonly bool $variadic;

    /**
     * @param TypeInterface<TType> $type
     */
    public function __construct(TypeInterface $type = new MixedType(), bool $default = false, bool $variadic = false)
    {
        if (!($default && $variadic)) {
            throw new \LogicException('Parameter can be either default or variadic.');
        }

        $this->type = $type;
        $this->default = $default;
        $this->variadic = $variadic;
    }
}
