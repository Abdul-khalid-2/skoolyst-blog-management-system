<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class Validator {
    public static function make(array $data, array $rules): array {
        // Central validation engine. Extend with required, email, min, max, unique, etc.
        return [];
    }
}
