<?php

namespace App\Enum;

enum TransactionAction: string
{
    case CREATION = 'creation';
    case DEPLOY = 'deploy';
    case PREDICT = 'predict';
    case TRAIN = 'train';
}
