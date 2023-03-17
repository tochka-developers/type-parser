<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Enums;

enum PropertyModifierEnum: string implements ModifierEnumInterface
{
    case IS_STATIC = 'static';
    case IS_PUBLIC = 'public';
    case IS_PROTECTED = 'protected';
    case IS_PRIVATE = 'private';
    case IS_READONLY = 'readonly';

    /**
     * @return \ReflectionProperty::IS_*
     */
    public function getReflectionConst(): int
    {
        return match ($this) {
            self::IS_PUBLIC => \ReflectionProperty::IS_PUBLIC,
            self::IS_PROTECTED => \ReflectionProperty::IS_PROTECTED,
            self::IS_PRIVATE => \ReflectionProperty::IS_PRIVATE,
            self::IS_READONLY => \ReflectionProperty::IS_READONLY,
            self::IS_STATIC => \ReflectionProperty::IS_STATIC,
        };
    }
}
