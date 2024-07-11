<?php

namespace App\Enum;

enum ModelType: string
{
    case CLASSIFICATION = 'Classification';
    case REGRESSION = 'Regression';

    public static function all(): array
    {
        return [
            self::CLASSIFICATION,
            self::REGRESSION,
        ];
    }
}
