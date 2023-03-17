<?php

declare(strict_types=1);

namespace Tochka\TypeParser\TypeSystem\DTO;

enum BoolRestrictionEnum: string
{
    case NONE = 'none';
    case TRUE = 'true';
    case FALSE = 'false';
}
