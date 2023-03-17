<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeFactories;

use Tochka\TypeParser\Contracts\ExtendedReflectionInterface;
use Tochka\TypeParser\Exceptions\LogicException;
use Tochka\TypeParser\TypeSystem\DTO\BoolRestrictionEnum;
use Tochka\TypeParser\TypeSystem\TypeInterface;
use Tochka\TypeParser\TypeSystem\Types\ArrayType;
use Tochka\TypeParser\TypeSystem\Types\BoolType;
use Tochka\TypeParser\TypeSystem\Types\CallableType;
use Tochka\TypeParser\TypeSystem\Types\FloatType;
use Tochka\TypeParser\TypeSystem\Types\IntersectionType;
use Tochka\TypeParser\TypeSystem\Types\IntType;
use Tochka\TypeParser\TypeSystem\Types\MixedType;
use Tochka\TypeParser\TypeSystem\Types\NamedObjectType;
use Tochka\TypeParser\TypeSystem\Types\NeverType;
use Tochka\TypeParser\TypeSystem\Types\NullType;
use Tochka\TypeParser\TypeSystem\Types\ObjectType;
use Tochka\TypeParser\TypeSystem\Types\ResourceType;
use Tochka\TypeParser\TypeSystem\Types\StringType;
use Tochka\TypeParser\TypeSystem\Types\UnionType;
use Tochka\TypeParser\TypeSystem\Types\VoidType;

/**
 * @psalm-api
 */
class ReflectionTypeFactoryMiddleware implements TypeFactoryMiddlewareInterface
{
    public function handle(
        TypeInterface $defaultType,
        ExtendedReflectionInterface $reflector,
        callable $next
    ): TypeInterface {
        $originalReflector = $reflector->getReflection();

        if (!$originalReflector instanceof \ReflectionParameter && !$originalReflector instanceof \ReflectionProperty) {
            if (!$originalReflector instanceof \ReflectionMethod) {
                return $next($defaultType, $reflector);
            }
            $reflectionType = $originalReflector->getReturnType();
        } else {
            $reflectionType = $originalReflector->getType();
        }

        if ($reflectionType === null) {
            return $next($defaultType, $reflector);
        }

        return $next(
            $this->getType($reflectionType) ?? $defaultType,
            $reflector
        );
    }

    private function getType(\ReflectionType $reflectionType): ?TypeInterface
    {
        if ($reflectionType instanceof \ReflectionUnionType) {
            return new UnionType($this->getMultipleTypes($reflectionType));
        }
        if ($reflectionType instanceof \ReflectionIntersectionType) {
            return new IntersectionType($this->getMultipleTypes($reflectionType));
        }
        if ($reflectionType instanceof \ReflectionNamedType) {
            $type = $this->getNamedType($reflectionType);
            if (!$type instanceof NullType && $reflectionType->allowsNull()) {
                return new UnionType([new NullType(), $type]);
            }

            return $type;
        }

        return null;
    }

    /**
     * @return non-empty-list<TypeInterface>
     */
    private function getMultipleTypes(\ReflectionUnionType|\ReflectionIntersectionType $reflectionUnionType): array
    {
        $types = [];
        foreach ($reflectionUnionType->getTypes() as $reflectionType) {
            $type = $this->getType($reflectionType);

            if ($type === null) {
                continue;
            }

            $types[] = $type;
        }

        if (empty($types)) {
            throw new LogicException('UnionType|IntersectionType must contain at least two types');
        }

        return $types;
    }

    private function getNamedType(\ReflectionNamedType $reflectionType): TypeInterface
    {
        /**
         * @var ("array"|"iterable"|"bool"|"callable"|"Closure"|"\Closure"|"false"|"float"|"int"|"mixed"|"never"|"null"|"object"|"resource"|"string"|"true"|"void"|class-string) $name
         */
        $name = $reflectionType->getName();

        return match ($name) {
            'array', 'iterable' => new ArrayType(),
            'bool' => new BoolType(),
            'callable', 'Closure', '\Closure' => new CallableType(),
            'false' => new BoolType(BoolRestrictionEnum::FALSE),
            'float' => new FloatType(),
            'int' => new IntType(),
            'mixed' => new MixedType(),
            'never' => new NeverType(),
            'null' => new NullType(),
            'object' => new ObjectType(),
            'resource' => new ResourceType(),
            'string' => new StringType(),
            'true' => new BoolType(BoolRestrictionEnum::TRUE),
            'void' => new VoidType(),
            default => new NamedObjectType($name)
        };
    }
}
