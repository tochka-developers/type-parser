<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Contracts;

use phpDocumentor\Reflection\DocBlock;
use Tochka\TypeParser\Collection;

/**
 * @psalm-api
 */
interface ExtendedReflectionInterface
{
    public function getName(): string;

    public function getDescription(): ?string;

    public function getReflection(): \Reflector;

    public function getDocBlock(): ?DocBlock;

    /**
     * @return Collection<object>
     */
    public function getAttributes(): Collection;
}
