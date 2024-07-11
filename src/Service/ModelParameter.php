<?php

namespace App\Service;

class ModelParameter
{
    public function __construct(
        public string $name,
        public string $type,
        public $default,
        public ?string $constraint = null,
    ) {}
}
