<?php

namespace App\Enum;

enum ModelType: string
{
    case CLASSIFICATION = 'Classification';
    case NLP = 'Natural Language Processing';
    case REGRESSION = 'Regression';
    case TIME_SERIES = 'Time Series';

    public static function all(): array
    {
        return [
            self::CLASSIFICATION,
            self::NLP,
            self::REGRESSION,
            self::TIME_SERIES,
        ];
    }
}
