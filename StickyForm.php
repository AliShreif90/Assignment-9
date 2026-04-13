<?php
require_once 'Validation.php';

// StickyForm extends Validation so that a single object can both
// manage form field state (sticky behavior) and run validation rules.
// This avoids duplicating validation logic and lets index.php use
// one class for the entire form lifecycle.
class StickyForm extends Validation
{

    // Holds the current value for every registered field
    private $fields = [];

    // Holds the error message (or empty string) for every registered field
    private $errors = [];

    // Master status tracking object shared across all fields
    // 'error' => bool: true if ANY field has failed validation
    private $masterStatus = ['error' => false];

    // ----------------------------------------------------------------
    // registerField($name, $defaultValue)
    //   Registers a form field with an optional default / hardcoded value.
    //   If the form has been submitted ($_POST), the POST value is used.
    //   Otherwise the default is used.
    // ----------------------------------------------------------------
    public function registerField($name, $defaultValue = '')
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->fields[$name] = isset($_POST[$name]) ? trim($_POST[$name]) : '';
        } else {
            $this->fields[$name] = $defaultValue;
        }
        $this->errors[$name] = '';
    }

    // ----------------------------------------------------------------
    // getValue($name)
    //   Returns the current (sticky) value for a field so it can be
    //   echoed back into the HTML input's value attribute.
    // ----------------------------------------------------------------
    public function getValue($name)
    {
        return htmlspecialchars($this->fields[$name] ?? '');
    }

    // ----------------------------------------------------------------
    // setError($name, $message)
    //   Records an error message for a field and sets the master
    //   error flag to true so the form knows not to submit.
    // ----------------------------------------------------------------
    public function setError($name, $message)
    {
        $this->errors[$name] = $message;
        $this->masterStatus['error'] = true;
    }

    // ----------------------------------------------------------------
    // getError($name)
    //   Returns the error message for a field (empty string if none).
    // ----------------------------------------------------------------
    public function getError($name)
    {
        return $this->errors[$name] ?? '';
    }

    // ----------------------------------------------------------------
    // getMasterStatus()
    //   Returns the masterStatus array.
    //   masterStatus['error'] is true if ANY field failed validation.
    // ----------------------------------------------------------------
    public function getMasterStatus()
    {
        return $this->masterStatus;
    }

    // ----------------------------------------------------------------
    // isSubmitted()
    //   Returns true if the form was submitted via POST.
    // ----------------------------------------------------------------
    public function isSubmitted()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // ----------------------------------------------------------------
    // clearFields()
    //   Resets all field values to empty strings after a successful
    //   insertion so the form appears blank to the user.
    // ----------------------------------------------------------------
    public function clearFields()
    {
        foreach ($this->fields as $name => $val) {
            $this->fields[$name] = '';
        }
    }
}
