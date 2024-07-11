<?php

namespace App\Service;

use App\Enum\ModelName;
use App\Enum\ModelType;

class ModelParameters
{
    public static function classification($modelName): array {
        switch ($modelName)
        {
            case ModelName::DT:
                return [
                    new ModelParameter('max_depth', 'int', null),
                    new ModelParameter('min_samples_split', 'int', 2),
                    new ModelParameter('min_samples_leaf', 'int', 1),
                ];
            case ModelName::KNN:
                return [
                    new ModelParameter('n_neighbors', 'int', 5),
                    new ModelParameter('algorithm', 'string', 'auto', '{"auto", "ball_tree", "kd_tree", "brute"}'),
                    new ModelParameter('leaf_size', 'int', 30),
                ];
            case ModelName::LR:
                return [
                    new ModelParameter('C', 'float', 1.0),
                    new ModelParameter('solver', 'string', 'lbfgs', '{"lbfgs", "liblinear", "newton-cg", "newton-cholesky", "sag", "saga"}'),
                    new ModelParameter('max_iter', 'int', 100),
                ];
            case ModelName::RF:
                return [
                    new ModelParameter('n_estimators', 'int', 100),
                    new ModelParameter('max_depth', 'int', null),
                    new ModelParameter('min_samples_split', 'int', 2),
                    new ModelParameter('min_samples_leaf', 'int', 1),
                ];
            case ModelName::CNN:
                return [
                    new ModelParameter('conv_filters', 'int', 64),
                    new ModelParameter('kernel_size', 'int', 7),
                    new ModelParameter('pool_size', 'int', 3),
                    new ModelParameter('num_conv_layers', 'int', 2),
                    new ModelParameter('hidden_units', 'int', 64),
                    new ModelParameter('optimizer', 'string', 'Adam'),
                    new ModelParameter('loss', 'string', 'categorical_crossentropy'),
                    new ModelParameter('epochs', 'int', 50),
                ];
            case ModelName::MLP:
                return [
                    new ModelParameter('hidden_units', 'int', 64),
                    new ModelParameter('input_shape', 'string', '2,'),
                    new ModelParameter('num_hidden_layers', 'int', 2),
                    new ModelParameter('optimizer', 'string', 'Adam'),
                    new ModelParameter('loss', 'string', 'categorical_crossentropy'),
                    new ModelParameter('epochs', 'int', 50),
                ];
            case ModelName::XGBOOST:
                return [
                    new ModelParameter('learning_rate', 'float', 0.01),
                    new ModelParameter('max_depth', 'int', 5),
                    new ModelParameter('min_child_weight', 'int', 1),
                    new ModelParameter('max_leaves', 'int', 0),
                ];
        }

        return [];
    }

    public static function regression($modelName): array {
        switch ($modelName)
        {
            case ModelName::BAYES_RIDGE:
                return [
                    new ModelParameter('max_iter', 'int', 300),
                    new ModelParameter('tol', 'float', 1e-03, 'tol >= 0'),
                    new ModelParameter('alpha_1', 'float', 1e-06, 'alpha_1 >= 0'),
                    new ModelParameter('alpha_2', 'float', 1e-06, 'alpha_2 >= 0'),
                    new ModelParameter('lambda_1', 'float', 1e-06, 'lambda_1 >= 0'),
                    new ModelParameter('lambda_2', 'float', 1e-06, 'lambda_2 >= 0'),
                    new ModelParameter('alpha_init', 'float', null),
                    new ModelParameter('lambda_init', 'float', null),
                    new ModelParameter('verbose', 'bool', false),
                ];
            case ModelName::DT:
                return [
                    new ModelParameter('max_depth', 'int', null),
                    new ModelParameter('min_samples_split', 'int', 2),
                    new ModelParameter('min_samples_leaf', 'int', 1),
                    new ModelParameter('max_features', 'int', null, ''),
                ];
            case ModelName::EN:
                return [
                    new ModelParameter('alpha', 'float', 1.0),
                    new ModelParameter('l1_ratio', 'float', 0.5, '0 <= l1_ratio <= 1'),
                    new ModelParameter('max_iter', 'int', 1000),
                    new ModelParameter('tol', 'float', 1e-04),
                ];
            case ModelName::XGBOOST:
                return [
                    new ModelParameter('loss', 'string', 'squared_error', '{"squared_error", "absolute_error", "huber", "quantile"}'),
                    new ModelParameter('learning_rate', 'float', 0.1, '[0.0, inf)'),
                    new ModelParameter('n_estimators', 'int', 100, '[1, inf)'),
                    new ModelParameter('subsample', 'float', 1.0, '(0.0, 1.0]'),
                ];
            case ModelName::LASSO:
                return [
                    new ModelParameter('alpha', 'float', 1.0, '[0, inf)'),
                    new ModelParameter('max_iter', 'int', 1000),
                    new ModelParameter('tol', 'float', 1e-04),
                ];
            case ModelName::LINEAR:
                return [
                    new ModelParameter('fit_intercept', 'bool', true),
                ];
            case ModelName::PR:
                return [
                    new ModelParameter('degree', 'int', 2),
                    new ModelParameter('include_bias', 'bool', true),
                    new ModelParameter('order', 'string', 'C', '{"C", "F"}'),
                ];
            case ModelName::RF:
                return [
                    new ModelParameter('n_estimators', 'int', 100),
                    new ModelParameter('criterion', 'string', 'squared_error', '{"squared_error", "absolute_error", "friedman_mse", "poisson"}'),
                    new ModelParameter('max_depth', 'int', null),
                    new ModelParameter('max_features', 'int', 1.0, ''),
                ];
            case ModelName::RR:
                return [
                    new ModelParameter('alpha', 'float', 1.0, '[0, inf)'),
                    new ModelParameter('max_iter', 'int', 100),
                    new ModelParameter('tol', 'float', 1e-04),
                    new ModelParameter('solver', 'string', 'auto', '{"auto", "svd", "cholesky", "lsqr", "sparse_cg", "sag", "saga", "lbfgs"}'),
                ];
            case ModelName::SVR:
                return [
                    new ModelParameter('kernel', 'string', 'rbf', '{"linear", "poly", "rbf", "sigmoid", "precomputed"}'),
                    new ModelParameter('degree', 'int', 3),
                    new ModelParameter('gamma', 'float', 'scale', ''),
                    new ModelParameter('epsilon', 'float', 0.1),
                ];
        }

        return [];
    }

    public static function get(ModelType $modelType, ModelName $modelName): array
    {
        switch ($modelType)
        {
            case ModelType::REGRESSION:
                return ModelParameters::regression($modelName);
            case ModelType::CLASSIFICATION:
                return ModelParameters::classification($modelName);
        }

        return [];
    }
}
