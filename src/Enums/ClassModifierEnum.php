<?php

declare(strict_types=1);

namespace Tochka\TypeParser\Enums;

enum ClassModifierEnum: string implements ModifierEnumInterface
{
    private const IS_READONLY_FOR_SUPPORT_8_1 = 65536;

    case IS_IMPLICIT_ABSTRACT = 'implicit_abstract';
    case IS_EXPLICIT_ABSTRACT = 'explicit_abstract';
    case IS_FINAL = 'final';
    case IS_READONLY = 'readonly';

    /**
     * @return \ReflectionClass::IS_*|self::IS_READONLY_FOR_SUPPORT_8_1
     */
    public function getReflectionConst(): int
    {
        return match ($this) {
            self::IS_IMPLICIT_ABSTRACT => \ReflectionClass::IS_IMPLICIT_ABSTRACT,
            self::IS_EXPLICIT_ABSTRACT => \ReflectionClass::IS_EXPLICIT_ABSTRACT,
            self::IS_FINAL => \ReflectionClass::IS_FINAL,
            self::IS_READONLY => self::IS_READONLY_FOR_SUPPORT_8_1,  // this const available since 8.2
        };
    }
}
