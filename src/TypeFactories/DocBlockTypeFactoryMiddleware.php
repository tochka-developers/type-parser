<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeFactories;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\PseudoTypes\CallableString;
use phpDocumentor\Reflection\PseudoTypes\False_;
use phpDocumentor\Reflection\PseudoTypes\HtmlEscapedString;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use phpDocumentor\Reflection\PseudoTypes\LiteralString;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;
use phpDocumentor\Reflection\PseudoTypes\NegativeInteger;
use phpDocumentor\Reflection\PseudoTypes\NonEmptyLowercaseString;
use phpDocumentor\Reflection\PseudoTypes\NonEmptyString;
use phpDocumentor\Reflection\PseudoTypes\NumericString;
use phpDocumentor\Reflection\PseudoTypes\PositiveInteger;
use phpDocumentor\Reflection\PseudoTypes\TraitString;
use phpDocumentor\Reflection\PseudoTypes\True_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\InterfaceString;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Never_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Resource_;
use phpDocumentor\Reflection\Types\Scalar;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\This;
use phpDocumentor\Reflection\Types\Void_;
use Tochka\TypeParser\Contracts\ExtendedReflectionInterface;
use Tochka\TypeParser\Exceptions\LogicException;
use Tochka\TypeParser\Reflectors\ExtendedMethodReflection;
use Tochka\TypeParser\Reflectors\ExtendedParameterReflection;
use Tochka\TypeParser\Reflectors\ExtendedPropertyReflection;
use Tochka\TypeParser\Traits\DocBlockOperationsTrait;
use Tochka\TypeParser\Traits\FullyQualifiedClassName;
use Tochka\TypeParser\TypeSystem\DTO\BoolRestrictionEnum;
use Tochka\TypeParser\TypeSystem\DTO\StringRestrictionEnum;
use Tochka\TypeParser\TypeSystem\TypeComparator;
use Tochka\TypeParser\TypeSystem\TypeInterface;
use Tochka\TypeParser\TypeSystem\Types\ArrayKeyType;
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
use Tochka\TypeParser\TypeSystem\Types\ScalarType;
use Tochka\TypeParser\TypeSystem\Types\StringType;
use Tochka\TypeParser\TypeSystem\Types\UnionType;
use Tochka\TypeParser\TypeSystem\Types\VoidType;

/**
 * @psalm-api
 */
class DocBlockTypeFactoryMiddleware implements TypeFactoryMiddlewareInterface
{
    use DocBlockOperationsTrait;
    use FullyQualifiedClassName;

    public function __construct(
        private readonly TypeComparator $typeComparator,
    ) {
    }

    public function handle(
        TypeInterface $defaultType,
        ExtendedReflectionInterface $reflector,
        callable $next
    ): TypeInterface {
        $type = $this->getDocBlockType($reflector);

        if ($type === null || $defaultType instanceof IntersectionType) {
            return $defaultType;
        }

        if ($defaultType instanceof UnionType) {
            // для каждого типа из выведенного надо найти в списке типов phpdoc соответствующий и попробовать с помощью него уточнить тип
            $typeFromDocBlock = $this->clarifyType(new MixedType(), $type, $reflector);

            /** @var list<TypeInterface> $resultTypes */
            $resultTypes = [];
            foreach ($defaultType->types as $subType) {
                $clarifiedType = $subType;
                if ($typeFromDocBlock instanceof UnionType) {
                    foreach ($typeFromDocBlock->types as $subDocType) {
                        if ($this->typeComparator->compare($subType, $subDocType)) {
                            $clarifiedType = $subDocType;
                            break;
                        }
                    }
                } elseif ($this->typeComparator->compare($subType, $typeFromDocBlock)) {
                    $clarifiedType = $typeFromDocBlock;
                }

                $resultTypes[] = $clarifiedType;
            }

            if (!empty($resultTypes)) {
                return new UnionType($resultTypes);
            }
        }

        return $next($this->clarifyType($defaultType, $type, $reflector), $reflector);
    }

    private function getDocBlockType(ExtendedReflectionInterface $reflector): ?Type
    {
        if ($reflector instanceof ExtendedParameterReflection) {
            /**
             * @psalm-ignore-var
             * @var Param|null $paramTag
             */
            $paramTag = $this->getTagsFromDocBlock($reflector->getDeclaringMethod()->getDocBlock())
                ->type(Param::class)
                ->filter(fn (Param $param) => $param->getVariableName() === $reflector->getName())
                ->first();

            return $paramTag?->getType();
        }

        if ($reflector instanceof ExtendedPropertyReflection) {
            $propertyTag = $this->getTagsFromDocBlock($reflector->getDocBlock())
                ->type(Var_::class)
                ->first();

            return $propertyTag?->getType();
        }

        if ($reflector instanceof ExtendedMethodReflection) {
            $returnTag = $this->getTagsFromDocBlock($reflector->getDocBlock())
                ->type(Return_::class)
                ->first();

            return $returnTag?->getType();
        }

        return null;
    }

    /**
     * @template TType
     * @param TypeInterface<TType> $defaultType
     * @param Type $type
     * @param ExtendedReflectionInterface $reflector
     * @return TypeInterface<TType>
     */
    private function clarifyType(
        TypeInterface $defaultType,
        Type $type,
        ExtendedReflectionInterface $reflector
    ): TypeInterface {
        if ($type instanceof Compound) {
            // if default type is not mixed, and doc block contains aggregate type - this is conflict, return default type
            if (!$defaultType instanceof MixedType) {
                return $defaultType;
            }

            return new UnionType($this->getMultipleTypes($defaultType, $type, $reflector));
        }

        if ($type instanceof Intersection) {
            // if default type is not mixed, and doc block contains aggregate type - this is conflict, return default type
            if (!$defaultType instanceof MixedType) {
                return $defaultType;
            }

            return new IntersectionType($this->getMultipleTypes($defaultType, $type, $reflector));
        }

        $clarifyType = $this->clarifySimpleType($type, $reflector);

        if ($defaultType instanceof MixedType || $this->typeComparator->compare($defaultType, $clarifyType)) {
            return $clarifyType;
        }

        return $defaultType;
    }

    private function clarifySimpleType(Type $type, ExtendedReflectionInterface $reflector): TypeInterface
    {
        if ($type instanceof Array_ || $type instanceof Iterable_) {
            return $this->clarifyArrayType($type, $reflector);
        }

        if ($type instanceof Boolean) {
            return $this->clarifyBoolType($type);
        }

        if ($type instanceof Callable_) {
            return new CallableType();
        }

        if ($type instanceof Float_) {
            return new FloatType();
        }

        if ($type instanceof Integer) {
            return $this->clarifyIntegerType($type);
        }

        if ($type instanceof Never_) {
            return new NeverType();
        }

        if ($type instanceof Null_) {
            return new NullType();
        }

        if ($type instanceof Object_ || $type instanceof Self_ || $type instanceof Static_ || $type instanceof This) {
            return $this->clarifyObjectType($type, $reflector);
        }

        if ($type instanceof Resource_) {
            return new ResourceType();
        }

        if ($type instanceof String_ || $type instanceof InterfaceString) {
            return $this->clarifyStringType($type);
        }

        if ($type instanceof Void_) {
            return new VoidType();
        }

        if ($type instanceof Scalar) {
            return new ScalarType();
        }

        return new MixedType();
    }

    /**
     * @return non-empty-list<TypeInterface>
     */
    private function getMultipleTypes(
        TypeInterface $defaultType,
        Compound|Intersection $type,
        ExtendedReflectionInterface $reflector
    ): array {
        $types = [];
        foreach ($type->getIterator() as $subType) {
            $types[] = $this->clarifyType($defaultType, $subType, $reflector);
        }

        if (empty($types)) {
            throw new LogicException('Compound|Intersection docBlock type must contain at least two types');
        }

        return $types;
    }

    private function clarifyArrayType(
        AbstractList $type,
        ExtendedReflectionInterface $reflector
    ): TypeInterface {
        return new ArrayType(
            $this->clarifyType(new ArrayKeyType(), $type->getKeyType(), $reflector),
            $this->clarifyType(new MixedType(), $type->getValueType(), $reflector),
        );
    }

    private function clarifyBoolType(Boolean $type): TypeInterface
    {
        if ($type instanceof False_) {
            return new BoolType(BoolRestrictionEnum::FALSE);
        }

        if ($type instanceof True_) {
            return new BoolType(BoolRestrictionEnum::TRUE);
        }

        return new BoolType();
    }

    private function clarifyIntegerType(Integer $type): TypeInterface
    {
        if ($type instanceof IntegerRange) {
            $min = $type->getMinValue() ? (int)$type->getMinValue() : null;
            $max = $type->getMaxValue() ? (int)$type->getMaxValue() : null;
            return new IntType($min, $max);
        }

        if ($type instanceof PositiveInteger) {
            return new IntType(0);
        }

        if ($type instanceof NegativeInteger) {
            return new IntType(null, 0);
        }

        return new IntType();
    }

    public function clarifyStringType(String_|InterfaceString|ClassString $type): TypeInterface
    {
        $restriction = match ($type::class) {
            ClassString::class => StringRestrictionEnum::CLASS_NAME,
            LiteralString::class => StringRestrictionEnum::LITERAL,
            NonEmptyLowercaseString::class => StringRestrictionEnum::NON_EMPTY_LOWERCASE,
            TraitString::class => StringRestrictionEnum::TRAIT_NAME,
            LowercaseString::class => StringRestrictionEnum::LOWERCASE,
            CallableString::class => StringRestrictionEnum::CALLABLE,
            HtmlEscapedString::class => StringRestrictionEnum::HTML_ESCAPED,
            NumericString::class => StringRestrictionEnum::NUMERIC,
            NonEmptyString::class => StringRestrictionEnum::NON_EMPTY,
            InterfaceString::class => StringRestrictionEnum::INTERFACE_NAME,
            default => StringRestrictionEnum::NONE,
        };

        return new StringType($restriction);
    }

    private function clarifyObjectType(
        Object_|Self_|Static_|This $type,
        ExtendedReflectionInterface $reflector
    ): TypeInterface {
        if ($type instanceof Self_ || $type instanceof Static_ || $type instanceof This) {
            if ($reflector instanceof ExtendedParameterReflection) {
                return new NamedObjectType($reflector->getDeclaringMethod()->getDeclaringClass()->getName());
            }
            if ($reflector instanceof ExtendedPropertyReflection) {
                return new NamedObjectType($reflector->getDeclaringClass()->getName());
            }
            return new ObjectType();
        }

        if ($type->getFqsen() !== null) {
            /** @var class-string $className */
            $className = (string)$type->getFqsen();
            return new NamedObjectType($this->fullyQualifiedClassName($className));
        }

        return new ObjectType();
    }
}
