<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeFactories;

use Tochka\TypeParser\Contracts\ExtendedReflectionInterface;
use Tochka\TypeParser\TypeSystem\TypeInterface;

interface TypeFactoryMiddlewareInterface
{
    /**
     * @param TypeInterface $defaultType
     * @param ExtendedReflectionInterface $reflector
     * @param callable(TypeInterface, ExtendedReflectionInterface): TypeInterface $next
     * @return TypeInterface
     */
    public function handle(
        TypeInterface $defaultType,
        ExtendedReflectionInterface $reflector,
        callable $next
    ): TypeInterface;
}
