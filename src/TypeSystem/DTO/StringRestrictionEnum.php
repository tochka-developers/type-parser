<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\DTO;

enum StringRestrictionEnum: string
{
    case NONE = 'none';
    case CLASS_NAME = 'class';
    case INTERFACE_NAME = 'interface';
    case TRAIT_NAME = 'trait';
    case ENUM_NAME = 'enum';
    case LITERAL = 'literal';
    case NON_EMPTY = 'non_empty';
    case NON_EMPTY_LOWERCASE = 'non_empty_lowercase';
    case LOWERCASE = 'lowercase';
    case CALLABLE = 'callable';
    case HTML_ESCAPED = 'html_escaped';
    case NUMERIC = 'numeric';
}
