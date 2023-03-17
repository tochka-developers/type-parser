<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Reflectors;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Tochka\TypeParser\Collection;
use Tochka\TypeParser\Contracts\AttributeReaderInterface;
use Tochka\TypeParser\Contracts\ExtendedValueReflectionInterface;
use Tochka\TypeParser\ExtendedTypeFactory;
use Tochka\TypeParser\Traits\DocBlockOperationsTrait;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 */
class ExtendedParameterReflection implements ExtendedValueReflectionInterface
{
    use DocBlockOperationsTrait;

    private ?DocBlock $docBlock;

    public function __construct(
        private readonly ExtendedMethodReflection $declaringMethodReflection,
        private readonly \ReflectionParameter $reflection,
        private readonly AttributeReaderInterface $attributeReader,
        DocBlockFactoryInterface $docBlockFactory,
        private readonly ExtendedTypeFactory $extendedTypeFactory,
    ) {
        $this->docBlock = $this->createDocBlock($reflection, $docBlockFactory);
    }

    public function getReflection(): \ReflectionParameter
    {
        return $this->reflection;
    }

    public function getDeclaringMethod(): ExtendedMethodReflection
    {
        return $this->declaringMethodReflection;
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function getDescription(): ?string
    {
        /**
         * @psalm-ignore-var
         * @var Param|null $paramTag
         */
        $paramTag = $this->getTagsFromDocBlock($this->getDeclaringMethod()->getDocBlock())
            ->type(Param::class)
            ->filter(fn (Param $param) => $param->getName() === $this->getName())
            ->first();

        $description = $paramTag?->getDescription()?->getBodyTemplate();

        return !empty($description) ? $description : null;
    }

    public function getType(): TypeInterface
    {
        return $this->extendedTypeFactory->getType($this);
    }

    public function hasDefaultValue(): bool
    {
        return $this->reflection->isOptional();
    }

    public function isRequired(): bool
    {
        return !$this->reflection->isOptional();
    }

    public function getDefaultValue(): mixed
    {
        try {
            return $this->reflection->getDefaultValue();
        } catch (\ReflectionException) {
            return null;
        }
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
        return $this->attributeReader->getParameterMetadata($this->reflection);
    }
}
