<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Reflectors;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Tochka\TypeParser\Collection;
use Tochka\TypeParser\Contracts\AttributeReaderInterface;
use Tochka\TypeParser\Contracts\ExtendedReflectionInterface;
use Tochka\TypeParser\Enums\MethodModifierEnum;
use Tochka\TypeParser\ExtendedTypeFactory;
use Tochka\TypeParser\Traits\DocBlockOperationsTrait;
use Tochka\TypeParser\Traits\ModifiersTrait;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 */
class ExtendedMethodReflection implements ExtendedReflectionInterface
{
    use DocBlockOperationsTrait;
    use ModifiersTrait;

    private ?DocBlock $docBlock;

    public function __construct(
        private readonly ExtendedClassReflection $declaringClassReflection,
        private readonly \ReflectionMethod $reflection,
        private readonly AttributeReaderInterface $attributeReader,
        private readonly DocBlockFactoryInterface $docBlockFactory,
        private readonly ExtendedTypeFactory $extendedTypeFactory,
    ) {
        $this->docBlock = $this->createDocBlock($reflection, $docBlockFactory);
    }

    public function getReflection(): \ReflectionMethod
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

    /**
     * @return Collection<ExtendedParameterReflection>
     */
    public function getParameters(): Collection
    {
        return new Collection(
            array_map(
                fn (\ReflectionParameter $parameter) => new ExtendedParameterReflection(
                    $this,
                    $parameter,
                    $this->attributeReader,
                    $this->docBlockFactory,
                    $this->extendedTypeFactory,
                ),
                $this->reflection->getParameters()
            )
        );
    }

    public function getReturnType(): TypeInterface
    {
        return $this->extendedTypeFactory->getType($this);
    }

    public function getReturnDescription(): ?string
    {
        /**
         * @psalm-ignore-var
         * @var Return_|null $returnTag
         */
        $returnTag = $this->getTagsFromDocBlock($this->getDocBlock())
            ->type(Return_::class)
            ->first();

        $description = $returnTag?->getDescription()?->getBodyTemplate();

        return !empty($description) ? $description : null;
    }

    public function hasModifier(MethodModifierEnum $methodModifier, MethodModifierEnum ...$methodModifiers): bool
    {
        return $this->checkModifiers($this->reflection->getModifiers(), $methodModifier, ...$methodModifiers);
    }

    public function getDescription(): ?string
    {
        return $this->getDescriptionFromDocBlock($this->docBlock);
    }

    public function getDocBlock(): ?DocBlock
    {
        return $this->docBlock;
    }

    /**
     * @return Collection<object>
     */
    public function getAttributes(): Collection
    {
        return $this->attributeReader->getFunctionMetadata($this->reflection);
    }
}
