<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \RuntimeException
{
    public function __construct(string $message, ?ContainerExceptionInterface $previous = null)
    {
        parent::__construct($message, 30100, $previous);
    }
}
