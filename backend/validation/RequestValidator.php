<?php
/**
 * Request Validator Class
 * 
 * Centralized input validation dan sanitization untuk mencegah:
 * - XSS attacks
 * - SQL injection
 * - Type mismatch
 * - Invalid data formats
 */

class RequestValidator {
    private static $errors = [];

    /**
     * Validate multiple fields sesuai rules
     * 
     * @param array $data Data yang akan divalidasi
     * @param array $rules Validation rules
     * @return array Validated dan sanitized data, atau throw exception jika invalid
     */
    public static function validate($data, $rules) {
        self::$errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            // Convert rules string to array
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            // Apply validators
            foreach ($fieldRules as $rule) {
                $value = self::applyRule($field, $value, $rule, $data);
                
                if ($value === false) {
                    break; // Stop processing if validation fails
                }
            }

            // If validation passed, add to validated data
            if ($value !== false) {
                $validated[$field] = $value;
            }
        }

        // Throw exception jika ada errors
        if (!empty(self::$errors)) {
            throw new ValidationException('Validation failed: ' . implode('; ', self::$errors));
        }

        return $validated;
    }

    /**
     * Apply single validation rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Rule definition (e.g., "required", "email", "integer", "max:100")
     * @param array $data Full data array (for conditional rules)
     * @return mixed Sanitized value atau false jika invalid
     */
    private static function applyRule($field, $value, $rule, $data) {
        $rule = trim($rule);

        // Parse rule dengan parameters (e.g., "max:100" -> rule="max", param="100")
        if (strpos($rule, ':') !== false) {
            list($ruleName, $param) = explode(':', $rule, 2);
            $ruleName = trim($ruleName);
            $param = trim($param);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    self::$errors[] = ucfirst($field) . ' is required';
                    return false;
                }
                return $value;

            case 'nullable':
                return $value; // Allow null values

            case 'string':
                if (!is_null($value) && !is_string($value)) {
                    self::$errors[] = ucfirst($field) . ' must be a string';
                    return false;
                }
                return self::sanitizeString($value);

            case 'integer':
            case 'int':
                if (!is_null($value) && !is_numeric($value)) {
                    self::$errors[] = ucfirst($field) . ' must be an integer';
                    return false;
                }
                return is_null($value) ? $value : (int)$value;

            case 'numeric':
                if (!is_null($value) && !is_numeric($value)) {
                    self::$errors[] = ucfirst($field) . ' must be numeric';
                    return false;
                }
                return $value;

            case 'email':
                if (!is_null($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    self::$errors[] = ucfirst($field) . ' must be a valid email';
                    return false;
                }
                return $value;

            case 'url':
                if (!is_null($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    self::$errors[] = ucfirst($field) . ' must be a valid URL';
                    return false;
                }
                return $value;

            case 'min':
                if (!is_null($value)) {
                    $length = is_string($value) ? strlen($value) : $value;
                    if ($length < (int)$param) {
                        self::$errors[] = ucfirst($field) . ' must be at least ' . $param;
                        return false;
                    }
                }
                return $value;

            case 'max':
                if (!is_null($value)) {
                    $length = is_string($value) ? strlen($value) : $value;
                    if ($length > (int)$param) {
                        self::$errors[] = ucfirst($field) . ' must not exceed ' . $param;
                        return false;
                    }
                }
                return $value;

            case 'in':
                $allowed = explode(',', $param);
                $allowed = array_map('trim', $allowed);
                if (!is_null($value) && !in_array($value, $allowed, true)) {
                    self::$errors[] = ucfirst($field) . ' must be one of: ' . $param;
                    return false;
                }
                return $value;

            case 'date':
                if (!is_null($value)) {
                    if (!self::isValidDate($value)) {
                        self::$errors[] = ucfirst($field) . ' must be a valid date (YYYY-MM-DD)';
                        return false;
                    }
                }
                return $value;

            case 'phone':
                if (!is_null($value)) {
                    $cleaned = preg_replace('/[^0-9+\-\s]/', '', $value);
                    if (strlen($cleaned) < 7) {
                        self::$errors[] = ucfirst($field) . ' must be a valid phone number';
                        return false;
                    }
                    return $cleaned;
                }
                return $value;

            case 'confirmed':
                // Check jika ada field_confirmation dengan nilai sama
                $confirmationField = $field . '_confirmation';
                if (($data[$confirmationField] ?? null) !== $value) {
                    self::$errors[] = ucfirst($field) . ' confirmation does not match';
                    return false;
                }
                return $value;

            case 'array':
                if (!is_null($value) && !is_array($value)) {
                    self::$errors[] = ucfirst($field) . ' must be an array';
                    return false;
                }
                return $value;

            case 'sanitize':
                return self::sanitizeString($value);

            case 'escape':
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

            default:
                return $value; // Unknown rule, pass through
        }
    }

    /**
     * Sanitize string: remove harmful characters
     * 
     * @param mixed $value String value
     * @return mixed Sanitized value
     */
    public static function sanitizeString($value) {
        if (is_null($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map([self::class, 'sanitizeString'], $value);
        }

        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove extra spaces
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     * 
     * @param string $date
     * @return bool
     */
    private static function isValidDate($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Get validation errors
     * 
     * @return array
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Check jika ada errors
     * 
     * @return bool
     */
    public static function hasErrors() {
        return !empty(self::$errors);
    }
}

/**
 * Custom Exception untuk validation errors
 */
class ValidationException extends Exception {
    public function __construct($message = '', $code = 422) {
        parent::__construct($message, $code);
    }
}
?>
