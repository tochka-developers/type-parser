<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Traits;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\ContextFactory;
use Tochka\TypeParser\Collection;

trait DocBlockOperationsTrait
{
    private function createDocBlock(\Reflector $reflector, DocBlockFactoryInterface $docBlockFactory): ?DocBlock
    {
        if (!method_exists($reflector, 'getDocComment')) {
            return null;
        }

        $docComment = $reflector->getDocComment();

        if ($docComment === false) {
            return null;
        }

        $context = (new ContextFactory())->createFromReflector($reflector);
        try {
            return $docBlockFactory->create($docComment, $context);
        } catch (\Throwable) {
            return null;
        }
    }

    private function getDescriptionFromDocBlock(?DocBlock $docBlock): ?string
    {
        if ($docBlock === null) {
            return null;
        }

        $summary = $docBlock->getSummary();
        $description = $docBlock->getDescription()->getBodyTemplate();

        $resultDescription = implode(PHP_EOL . PHP_EOL, array_filter([$summary, $description]));

        return !empty($resultDescription) ? $resultDescription : null;
    }

    private function getTagsFromDocBlock(?DocBlock $docBlock): Collection
    {
        if ($docBlock === null) {
            return new Collection();
        }

        return new Collection($docBlock->getTags());
    }
}
