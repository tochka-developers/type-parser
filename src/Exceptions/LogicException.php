<?php

namespace Tochka\TypeParser\Exceptions;

class LogicException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 30200);
    }
}
