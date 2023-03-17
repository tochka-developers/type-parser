<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Reflectors;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Tochka\TypeParser\Collection;
use Tochka\TypeParser\Contracts\AttributeReaderInterface;
use Tochka\TypeParser\Contracts\ExtendedReflectionInterface;
use Tochka\TypeParser\Enums\ClassModifierEnum;
use Tochka\TypeParser\Enums\PropertyModifierEnum;
use Tochka\TypeParser\ExtendedTypeFactory;
use Tochka\TypeParser\Traits\DocBlockOperationsTrait;
use Tochka\TypeParser\Traits\ModifiersTrait;

/**
 * @psalm-api
 */
final class ExtendedClassReflection implements ExtendedReflectionInterface
{
    use DocBlockOperationsTrait;
    use ModifiersTrait;

    private ?DocBlock $docBlock;

    public function __construct(
        private readonly \ReflectionClass $reflection,
        private readonly AttributeReaderInterface $attributeReader,
        private readonly DocBlockFactoryInterface $docBlockFactory,
        private readonly ExtendedTypeFactory $extendedTypeFactory,
    ) {
        $this->docBlock = $this->createDocBlock($reflection, $docBlockFactory);
    }

    /**
     * @return class-string
     */
    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function hasModifiers(ClassModifierEnum $classModifier, ClassModifierEnum ...$classModifiers): bool
    {
        return $this->checkModifiers($this->reflection->getModifiers(), $classModifier, ...$classModifiers);
    }

    /**
     * @param PropertyModifierEnum ...$filter
     * @return Collection<ExtendedPropertyReflection>
     */
    public function getProperties(PropertyModifierEnum ...$filter): Collection
    {
        /** @var int-mask-of<\ReflectionProperty::IS_*> $reflectionFilters */
        $reflectionFilters = array_reduce(
            $filter,
            function (int|null $carry, PropertyModifierEnum $item): int {
                return $carry === null ? $item->getReflectionConst() : $carry | $item->getReflectionConst();
            },
            null
        );

        return new Collection(
            array_map(
                fn (\ReflectionProperty $property) => new ExtendedPropertyReflection(
                    $this,
                    $property,
                    $this->attributeReader,
                    $this->docBlockFactory,
                    $this->extendedTypeFactory,
                ),
                $this->reflection->getProperties($reflectionFilters)
            )
        );
    }

    /**
     * @return Collection<object>
     */
    public function getAttributes(): Collection
    {
        return $this->attributeReader->getClassMetadata($this->reflection);
    }

    public function getDescription(): ?string
    {
        return $this->getDescriptionFromDocBlock($this->docBlock);
    }

    public function getReflection(): \ReflectionClass
    {
        return $this->reflection;
    }

    public function getDocBlock(): ?DocBlock
    {
        return $this->docBlock;
    }
}
