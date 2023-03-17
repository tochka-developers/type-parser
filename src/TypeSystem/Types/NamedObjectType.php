<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @template-covariant TObject of object
 * @implements TypeInterface<TObject>
 */
final class NamedObjectType implements TypeInterface
{
    /**
     * @param class-string<TObject> $className
     */
    public function __construct(
        public readonly string $className
    ) {
    }

    public function __toString(): string
    {
        return $this->className;
    }
}
