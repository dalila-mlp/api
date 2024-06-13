<?php

namespace App\Enum;

enum ModelName: string
{
    case NN = 'Neural Network';
    case CNN = 'Convolutional Neural Network';
    case DNN = 'Deep Neural Network';
    case KNN = 'K Nearest Neighbors';
    case RNN = 'Recurrent Neural Network';

    case CATBOOST = 'Catboost';
    case XGBOOST = 'Extreme Gradient Boosting';

    case AE = 'Autoencoder';
    case VAE = 'Variational Autoencoder';

    case DT = 'Decision Tree';
    case GAN = 'Generative Adversarial Network';
    case GRU = 'Gated Recurrent Unit';
    case LGBM = 'Light Gradient Boosting Machine';
    case LR = 'Logistic Regression';
    case LSTM = 'Long Short Term Memory';
    case MLP = 'Multi Layer Perceptron';
    case NAIIVE_BAYES = 'Naiive Bayes';
    case RF = 'Random Forest';
    case RL = 'Reinforcement Learning';
    case SVM = 'Support Vector Machine';

    public static function all(): array
    {
        return [
            self::NN,
            self::CNN,
            self::DNN,
            self::KNN,
            self::RNN,
            self::CATBOOST,
            self::XGBOOST,
            self::AE,
            self::VAE,
            self::DT,
            self::GAN,
            self::GRU,
            self::LGBM,
            self::LR,
            self::LSTM,
            self::MLP,
            self::NAIIVE_BAYES,
            self::RF,
            self::RL,
            self::SVM,
        ];
    }
}
