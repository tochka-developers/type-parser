<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Enums;

enum MethodModifierEnum: string implements ModifierEnumInterface
{
    case IS_PUBLIC = 'public';
    case IS_PROTECTED = 'protected';
    case IS_PRIVATE = 'private';
    case IS_STATIC = 'static';
    case IS_FINAL = 'final';
    case IS_ABSTRACT = 'abstract';

    /**
     * @return \ReflectionMethod::IS_*
     */
    public function getReflectionConst(): int
    {
        return match ($this) {
            self::IS_PUBLIC => \ReflectionMethod::IS_PUBLIC,
            self::IS_PROTECTED => \ReflectionMethod::IS_PROTECTED,
            self::IS_PRIVATE => \ReflectionMethod::IS_PRIVATE,
            self::IS_STATIC => \ReflectionMethod::IS_STATIC,
            self::IS_FINAL => \ReflectionMethod::IS_FINAL,
            self::IS_ABSTRACT => \ReflectionMethod::IS_ABSTRACT,
        };
    }
}
