<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\Types;

use Tochka\TypeParser\TypeSystem\DTO\Parameter;
use Tochka\TypeParser\TypeSystem\TypeInterface;

/**
 * @psalm-api
 * @psalm-immutable
 * @template-covariant TReturn
 * @implements TypeInterface<callable(): TReturn>
 */
final class CallableType implements TypeInterface
{
    /**
     * @var list<Parameter>
     */
    public readonly array $parameters;

    /**
     * @param list<TypeInterface|Parameter> $parameters
     * @param TypeInterface<TReturn>|null $returnType
     */
    public function __construct(
        array $parameters = [],
        public readonly ?TypeInterface $returnType = null,
    ) {
        $this->parameters = array_map(
            static fn (TypeInterface|Parameter $parameter): Parameter => $parameter instanceof TypeInterface
                ? new Parameter($parameter)
                : $parameter,
            $parameters,
        );
    }

    public function __toString(): string
    {
        return 'callable';
    }
}
