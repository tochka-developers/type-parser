<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem;

use Tochka\TypeParser\TypeSystem\Types\ArrayType;
use Tochka\TypeParser\TypeSystem\Types\BoolType;
use Tochka\TypeParser\TypeSystem\Types\FloatType;
use Tochka\TypeParser\TypeSystem\Types\IntType;
use Tochka\TypeParser\TypeSystem\Types\MixedType;
use Tochka\TypeParser\TypeSystem\Types\NullType;
use Tochka\TypeParser\TypeSystem\Types\ObjectType;
use Tochka\TypeParser\TypeSystem\Types\ResourceType;
use Tochka\TypeParser\TypeSystem\Types\StringType;

/**
 * @psalm-api
 */
class TypeFromValue
{
    public function inferType(mixed $value): TypeInterface
    {
        $type = gettype($value);

        return match ($type) {
            'array' => new ArrayType(),
            'boolean' => new BoolType(),
            'double' => new FloatType(),
            'integer' => new IntType(),
            'NULL' => new NullType(),
            'object' => new ObjectType(),
            'resource', 'resource (closed)' => new ResourceType(),
            'string' => new StringType(),
            default => new MixedType(),
        };
    }
}
