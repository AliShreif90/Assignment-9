<?php
class Validation
{

    // Validates that a value is not empty and contains letters only (upper and lower case)
    // Returns true if valid, false otherwise
    public function validateName($value)
    {
        $pattern = '/^[a-zA-Z]+$/';
        return preg_match($pattern, trim($value)) === 1;
    }

    // Validates that a value matches standard email format: example@example.com
    // Returns true if valid, false otherwise
    public function validateEmail($value)
    {
        $pattern = '/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/';
        return preg_match($pattern, trim($value)) === 1;
    }

    // Validates that a password meets complexity requirements:
    // At least 8 characters, 1 uppercase letter, 1 symbol, and 1 number
    // Returns true if valid, false otherwise
    public function validatePassword($value)
    {
        // At least 8 characters
        if (strlen($value) < 8)
            return false;
        // At least 1 uppercase letter
        if (!preg_match('/[A-Z]/', $value))
            return false;
        // At least 1 number
        if (!preg_match('/[0-9]/', $value))
            return false;
        // At least 1 symbol
        if (!preg_match('/[\W_]/', $value))
            return false;

        return true;
    }
}
