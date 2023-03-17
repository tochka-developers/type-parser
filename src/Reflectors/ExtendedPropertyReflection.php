<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Reflectors;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Tochka\TypeParser\Collection;
use Tochka\TypeParser\Contracts\AttributeReaderInterface;
use Tochka\TypeParser\Contracts\ExtendedValueReflectionInterface;
use Tochka\TypeParser\Enums\PropertyModifierEnum;
use Tochka\TypeParser\ExtendedTypeFactory;
use Tochka\TypeParser\Traits\DocBlockOperationsTrait;
use Tochka\TypeParser\Traits\ModifiersTrait;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 */
class ExtendedPropertyReflection implements ExtendedValueReflectionInterface
{
    use DocBlockOperationsTrait;
    use ModifiersTrait;

    private ?DocBlock $docBlock;

    public function __construct(
        private readonly ExtendedClassReflection $declaringClassReflection,
        private readonly \ReflectionProperty $reflection,
        private readonly AttributeReaderInterface $attributeReader,
        private readonly DocBlockFactoryInterface $docBlockFactory,
        private readonly ExtendedTypeFactory $extendedTypeFactory,
    ) {
        $this->docBlock = $this->createDocBlock($reflection, $docBlockFactory);
    }

    public function getReflection(): \ReflectionProperty
    {
        return $this->reflection;
    }

    public function getDeclaringClass(): ExtendedClassReflection
    {
        return $this->declaringClassReflection;
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getType(): TypeInterface
    {
        return $this->extendedTypeFactory->getType($this);
    }

    public function hasDefaultValue(): bool
    {
        if ($this->reflection->isPromoted()) {
            $promotedParameter = $this->getPromotedParameter();
            if ($promotedParameter === null) {
                return false;
            }

            return $promotedParameter->hasDefaultValue();
        }

        return $this->reflection->hasDefaultValue();
    }

    public function isRequired(): bool
    {
        return !$this->hasDefaultValue();
    }

    public function getDefaultValue(): mixed
    {
        if ($this->reflection->isPromoted()) {
            $promotedParameter = $this->getPromotedParameter();
            if ($promotedParameter === null) {
                return false;
            }

            return $promotedParameter->getDefaultValue();
        }

        return $this->reflection->getDefaultValue();
    }

    public function getDescription(): ?string
    {
        // заберем описание из самого docBlock
        $description = $this->getDescriptionFromDocBlock($this->docBlock);

        if ($description !== null) {
            return $description;
        }

        // если нет - попробуем найти описание в теге @var
        /**
         * @psalm-ignore-var
         * @var Var_|null $varTag
         */
        $varTag = $this->getTagsFromDocBlock($this->docBlock)
            ->type(Var_::class)
            ->first();

        $description = $varTag?->getDescription()?->getBodyTemplate();

        return !empty($description) ? $description : null;
    }

    public function hasModifier(
        PropertyModifierEnum $propertyModifier,
        PropertyModifierEnum ...$propertyModifiers
    ): bool {
        return $this->checkModifiers($this->reflection->getModifiers(), $propertyModifier, ...$propertyModifiers);
    }

    /**
     * @return Collection<object>
     */
    public function getAttributes(): Collection
    {
        return $this->attributeReader->getPropertyMetadata($this->reflection);
    }

    private function getPromotedParameter(): ?ExtendedParameterReflection
    {
        $constructor = $this->reflection->getDeclaringClass()->getConstructor();
        if ($constructor === null) {
            return null;
        }

        $constructorReflection = new ExtendedMethodReflection(
            $this->declaringClassReflection,
            $constructor,
            $this->attributeReader,
            $this->docBlockFactory,
            $this->extendedTypeFactory,
        );
        /**
         * @var ExtendedParameterReflection
         */
        return $constructorReflection->getParameters()
            ->filter(fn (ExtendedParameterReflection $parameter) => $parameter->getName() === $this->getName())
            ->first();
    }

    public function getDocBlock(): ?DocBlock
    {
        return $this->docBlock;
    }
}
