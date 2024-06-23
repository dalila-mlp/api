<?php

namespace App\Enum;

enum ModelName: string
{
    case NN = 'Neural Network';
    case CNN = 'Convolutional Neural Network';
    case DNN = 'Deep Neural Network';
    case KNN = 'K Nearest Neighbors';
    case RNN = 'Recurrent Neural Network';
    case EN = 'Elastic Net';
    case DBN = 'Deep Belief Network';

    case CATBOOST = 'Catboost';
    case XGBOOST = 'Extreme Gradient Boosting';
    case GBM = 'Gradient Boosting Machine';
    case LGBM = 'Light Gradient Boosting Machine';

    case PCA = 'Principal Component Analysis';
    case LDA = 'Linear Discriminant Analysis';
    case QDA = 'Quadratic Discriminant Analysis';

    case AE = 'Autoencoder';
    case VAE = 'Variational Autoencoder';

    case LR = 'Logistic Regression';
    case RR = 'Ridge Regression';
    case LASSO = 'Lasso Regression';
    case PR = 'Polynomial Regression';
    case SVR = 'Support Vector Regression';
    case OLSR = 'Ordinary Least Squares Regression';

    case NAIVE_BAYES = 'Naive Bayes';
    case BAYES = 'Bayesian Network';

    case KMEANS = 'K Means';
    case KMEANS_C = 'K Means Clustering';

    case MARKOV = 'Markov Chain';
    case HMARKOV = 'Hidden Markov Chain';

    case DT = 'Decision Tree';
    case EXTRA = 'Extra Trees';
    case ISOLATION = 'Isolation Forest';
    case RF = 'Random Forest';

    case GAN = 'Generative Adversarial Network';
    case GRU = 'Gated Recurrent Unit';
    case LSTM = 'Long Short Term Memory';
    case MLP = 'Multi Layer Perceptron';
    case RL = 'Reinforcement Learning';
    case SVM = 'Support Vector Machine';
    case ADA = 'AdaBoost';
    case BAG = 'Bagging';
    case RBM = 'Restricted Boltzmann Machine';
    case DS = 'Decision Stump';
    case HC = 'Hierarchical Clustering';
    case TRANSFORMER = 'Transformer';
    case UMAP = 'Uniform Manifold Approximation and Projection';
    case DBSCAN = 'Density-Based Spatial Clustering of Applications with Noise';
    case GAUSSIAN = 'Gaussian Mixture Model';
    case MATRIX = 'Matrix Factorization';
    case DSNE = 't-Distributed Stochastic Neighbor Embedding';
    case NEURAL_TURING = 'Neural Turing Machine';
    case BERT = 'Bidirectional Encoder Representations from Transformers';
    case RLM = 'Recurrent Learning Model';
    case MLA = 'Meta Learning Algorithm';
    case NAS = 'Neural Architecture Search';
    case EA = 'Evolutive Algorithm';
    case SA = 'Simulated Annealing';
    case PSO = 'Particle Swarm Optimization';
    case ACO = 'Ant Colony Optimization';
    case FLS = 'Fuzzy Logic System';
    case SVD = 'Singular Value Decomposition';

    public static function all(): array
    {
        return [
            self::NN,
            self::CNN,
            self::DNN,
            self::KNN,
            self::RNN,
            self::EN,
            self::DBN,
            self::CATBOOST,
            self::XGBOOST,
            self::GBM,
            self::LGBM,
            self::PCA,
            self::LDA,
            self::QDA,
            self::AE,
            self::VAE,
            self::LR,
            self::RR,
            self::LASSO,
            self::PR,
            self::SVR,
            self::OLSR,
            self::NAIVE_BAYES,
            self::BAYES,
            self::KMEANS,
            self::KMEANS_C,
            self::MARKOV,
            self::HMARKOV,
            self::DT,
            self::EXTRA,
            self::ISOLATION,
            self::RF,
            self::GAN,
            self::GRU,
            self::LSTM,
            self::MLP,
            self::RL,
            self::SVM,
            self::ADA,
            self::BAG,
            self::RBM,
            self::DS,
            self::HC,
            self::TRANSFORMER,
            self::UMAP,
            self::DBSCAN,
            self::GAUSSIAN,
            self::MATRIX,
            self::DSNE,
            self::NEURAL_TURING,
            self::BERT,
            self::RLM,
            self::MLA,
            self::NAS,
            self::EA,
            self::SA,
            self::PSO,
            self::ACO,
            self::FLS,
            self::SVD,
        ];
    }
}
