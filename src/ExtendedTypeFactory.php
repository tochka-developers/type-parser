<?php

declare(strict_types=1);

namespace Tochka\TypeParser;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Tochka\TypeParser\Contracts\ExtendedReflectionInterface;
use Tochka\TypeParser\Exceptions\ContainerException;
use Tochka\TypeParser\TypeFactories\TypeFactoryMiddlewareInterface;
use Tochka\TypeParser\TypeSystem\TypeInterface;
use Tochka\TypeParser\TypeSystem\Types\MixedType;

/**
 * @psalm-api
 */
final class ExtendedTypeFactory
{

    /**
     * @param array<TypeFactoryMiddlewareInterface|class-string<TypeFactoryMiddlewareInterface>> $typeFactoryMiddleware
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $typeFactoryMiddleware = []
    ) {
    }

    public function getType(ExtendedReflectionInterface $reflector): TypeInterface
    {
        return $this->handle($this->typeFactoryMiddleware, new MixedType(), $reflector);
    }

    /**
     * @param array<TypeFactoryMiddlewareInterface|class-string<TypeFactoryMiddlewareInterface>> $middlewareList
     */
    private function handle(
        array $middlewareList,
        TypeInterface $defaultType,
        ExtendedReflectionInterface $reflector
    ): TypeInterface {
        $currentMiddleware = array_shift($middlewareList);

        if ($currentMiddleware !== null) {
            $middleware = $this->getOrMakeMiddleware($currentMiddleware);
            return $middleware->handle(
                $defaultType,
                $reflector,
                function (
                    TypeInterface $defaultType,
                    ExtendedReflectionInterface $reflector
                ) use ($middlewareList): TypeInterface {
                    return $this->handle($middlewareList, $defaultType, $reflector);
                }
            );
        }

        return $defaultType;
    }

    /**
     * @template T of TypeFactoryMiddlewareInterface
     * @param TypeFactoryMiddlewareInterface|class-string<T> $middleware
     */
    private function getOrMakeMiddleware(
        TypeFactoryMiddlewareInterface|string $middleware
    ): TypeFactoryMiddlewareInterface {
        if ($middleware instanceof TypeFactoryMiddlewareInterface) {
            return $middleware;
        }

        try {
            /** @var T $middlewareInstance */
            $middlewareInstance = $this->container->get($middleware);
        } catch (ContainerExceptionInterface $e) {
            throw new ContainerException(
                sprintf('Error while making [%s]: error binding resolution', $middleware),
                $e
            );
        }

        if (!$middlewareInstance instanceof TypeFactoryMiddlewareInterface) {
            throw new ContainerException(
                sprintf(
                    'Error while making TypeFactoryMiddleware: it must be implement interface [%s]',
                    TypeFactoryMiddlewareInterface::class
                )
            );
        }

        return $middlewareInstance;
    }
}
