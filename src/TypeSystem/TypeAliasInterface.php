<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem;

/**
 * @psalm-api
 * @psalm-immutable
 * @template-covariant TType
 * @extends  TypeInterface<TType>
 */
interface TypeAliasInterface extends TypeInterface
{
    /**
     * @return TypeInterface<TType>
     */
    public function type(): TypeInterface;
}
