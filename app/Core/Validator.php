<?php
declare(strict_types=1);

namespace Skoolyst\Core;

/**
 * Central validation engine.
 *
 * Usage: Validator::make($data, ['email' => 'required|email', 'age' => 'numeric|min:1']);
 * Returns an array of field => [messages]. Empty array means validation passed.
 */
class Validator {
    public static function make(array $data, array $rules): array {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $message = self::check($name, $value, $param, $data);
                if ($message !== null) {
                    $errors[$field][] = str_replace(':field', $field, $message);
                }
            }
        }

        return $errors;
    }

    private static function check(string $rule, mixed $value, ?string $param, array $data): ?string {
        return match ($rule) {
            'required' => ($value === null || $value === '') ? ':field is required.' : null,
            'email' => ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? ':field must be a valid email address.' : null,
            'numeric' => ($value !== null && $value !== '' && !is_numeric($value)) ? ':field must be numeric.' : null,
            'min' => ($value !== null && $value !== '' && is_numeric($param) && (is_numeric($value) ? (float) $value < (float) $param : mb_strlen((string) $value) < (int) $param)) ? ":field must be at least {$param}." : null,
            'max' => ($value !== null && $value !== '' && is_numeric($param) && (is_numeric($value) ? (float) $value > (float) $param : mb_strlen((string) $value) > (int) $param)) ? ":field may not be greater than {$param}." : null,
            'confirmed' => ($value !== ($data[$param ?? ''] ?? ($data[$param . '_confirmation'] ?? null)) && $value !== ($data['confirm_' . ($param ?? '')] ?? null)) ? ':field confirmation does not match.' : null,
            'in' => ($value !== null && $value !== '' && $param !== null && !in_array($value, explode(',', $param), true)) ? ':field is not a valid option.' : null,
            default => null,
        };
    }
}
