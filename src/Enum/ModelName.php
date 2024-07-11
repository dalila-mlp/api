<?php

namespace App\Enum;

enum ModelName: string
{
    case CNN = 'Convolutional Neural Network';
    case KNN = 'K Nearest Neighbors';
    case EN = 'Elastic Net';
    case XGBOOST = 'Extreme Gradient Boosting';
    case LR = 'Logistic Regression';
    case LINEAR = 'Linear Regression';
    case RR = 'Ridge Regression';
    case LASSO = 'Lasso Regression';
    case PR = 'Polynomial Regression';
    case SVR = 'Support Vector Regression';
    case BAYES = 'Bayesian Network';
    case BAYES_RIDGE = 'Bayesian Regression';
    case DT = 'Decision Tree';
    case RF = 'Random Forest';
    case MLP = 'Multi Layer Perceptron';
    case SVM = 'Support Vector Machine';

    public static function all(): array
    {
        return [
            self::CNN,
            self::KNN,
            self::EN,
            self::XGBOOST,
            self::LR,
            self::LINEAR,
            self::RR,
            self::LASSO,
            self::PR,
            self::SVR,
            self::BAYES,
            self::BAYES_RIDGE,
            self::DT,
            self::RF,
            self::MLP,
            self::SVM,
        ];
    }
}
