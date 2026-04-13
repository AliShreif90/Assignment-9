<?php
// ============================================================
//  index.php  –  User Registration Form
// ============================================================
require_once 'classes/StickyForm.php';
require_once 'classes/Pdo_methods.php';

// ----------------------------------------------------------------
// $formConfig stores metadata that drives the entire form lifecycle:
//
//   'fields'        => each field's name, label, type, and default
//                      (hardcoded) test value
//   'status'        => overall submission message shown above the form
//   'masterStatus'  => reference to StickyForm's masterStatus array;
//                      masterStatus['error'] is true if any field
//                      failed validation, preventing DB insertion
// ----------------------------------------------------------------
$formConfig = [
    'fields' => [
        'first_name' => ['label' => '*First Name', 'type' => 'text', 'default' => ''],
        'last_name' => ['label' => '*Last Name', 'type' => 'text', 'default' => ''],
        'email' => ['label' => '*Email', 'type' => 'text', 'default' => ''],
        'password' => ['label' => '*Password', 'type' => 'password', 'default' => ''],
        'confirm_password' => ['label' => '*Confirm Password', 'type' => 'password', 'default' => ''],
    ],
    'status' => '',
    'masterStatus' => ['error' => false],
];

// Instantiate the sticky form (which also inherits Validation)
$form = new StickyForm();

// Register every field (populates from $_POST on submission, or uses default)
foreach ($formConfig['fields'] as $name => $meta) {
    $form->registerField($name, $meta['default']);
}

// ----------------------------------------------------------------
// FORM PROCESSING – runs only on POST
// ----------------------------------------------------------------
if ($form->isSubmitted()) {

    // --- 1. Validate first name ---
    if (!$form->validateName($form->getValue('first_name'))) {
        $form->setError('first_name', 'You must enter a first name and it must be alpha characters only.');
    }

    // --- 2. Validate last name ---
    if (!$form->validateName($form->getValue('last_name'))) {
        $form->setError('last_name', 'You must enter a last name and it must be alpha characters only.');
    }

    // --- 3. Validate email format ---
    if (!$form->validateEmail($form->getValue('email'))) {
        $form->setError('email', 'You must enter a email address and it must be in the format of example@example.com.');
    }

    // --- 4. Validate password complexity ---
    if (!$form->validatePassword($form->getValue('password'))) {
        $form->setError('password', 'Must have at least (8 characters, 1 uppercase, 1 symbol, 1 number)');
    }

    // --- 5. Validate confirm password complexity ---
    if (!$form->validatePassword($form->getValue('confirm_password'))) {
        $form->setError('confirm_password', 'Must have at least (8 characters, 1 uppercase, 1 symbol, 1 number)');
    }

    // Sync masterStatus from the StickyForm object into $formConfig
    $formConfig['masterStatus'] = $form->getMasterStatus();

    // Only run the deeper checks if basic validation passed so far
    if (!$formConfig['masterStatus']['error']) {

        // --- 6. Passwords must match ---
        if ($form->getValue('password') !== $form->getValue('confirm_password')) {
            $form->setError('confirm_password', 'Your passwords do not match');
            $formConfig['masterStatus'] = $form->getMasterStatus();
        }
    }

    // Re-sync after the password-match check
    $formConfig['masterStatus'] = $form->getMasterStatus();

    if (!$formConfig['masterStatus']['error']) {

        // --- 7. Duplicate email check ---
        $existing = Pdo_methods::selectWhere('users', 'email', $form->getValue('email'));

        if (count($existing) > 0) {
            $formConfig['status'] = 'There is already a record with that email';
        } else {
            // --- 8. Hash password and insert record ---
            $hashedPassword = password_hash($form->getValue('password'), PASSWORD_BCRYPT);

            Pdo_methods::insert('users', [
                'first_name' => $form->getValue('first_name'),
                'last_name' => $form->getValue('last_name'),
                'email' => $form->getValue('email'),
                'password' => $hashedPassword,
            ]);

            $formConfig['status'] = 'You have been added to the database';

            // Clear fields after successful insertion
            $form->clearFields();
        }
    }
}

// Fetch all records for the table display (always shown after first successful insert)
$records = [];
try {
    $records = Pdo_methods::selectAll('users');
} catch (Exception $e) {
    // If the table doesn't exist yet, just show no records
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment 9 – User Registration</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            padding: 20px 30px;
        }

        p.notice {
            margin-bottom: 10px;
        }

        .status-msg {
            margin-bottom: 10px;
            color: #333;
        }

        /* ---- form layout ---- */
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .form-group label {
            margin-bottom: 4px;
            font-size: 13px;
            color: #555;
        }

        .form-group input {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 14px;
            width: 100%;
        }

        .error-msg {
            color: #c0392b;
            font-size: 13px;
            margin-top: 4px;
        }

        /* ---- button ---- */
        .btn-register {
            background-color: #2d7dd2;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .btn-register:hover {
            background-color: #1a5fa8;
        }

        /* ---- records table ---- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .no-records {
            margin-top: 6px;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <p class="notice">All fields are required.</p>

    <?php if (!empty($formConfig['status'])): ?>
        <p class="status-msg">
            <?= htmlspecialchars($formConfig['status']) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="index.php">

        <!-- Row 1: First Name | Last Name -->
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">*First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?= $form->getValue('first_name') ?>">
                <?php if ($form->getError('first_name')): ?>
                    <span class="error-msg">
                        <?= htmlspecialchars($form->getError('first_name')) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="last_name">*Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= $form->getValue('last_name') ?>">
                <?php if ($form->getError('last_name')): ?>
                    <span class="error-msg">
                        <?= htmlspecialchars($form->getError('last_name')) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Row 2: Email | Password | Confirm Password -->
        <div class="form-row">
            <div class="form-group">
                <label for="email">*Email</label>
                <input type="text" id="email" name="email" value="<?= $form->getValue('email') ?>">
                <?php if ($form->getError('email')): ?>
                    <span class="error-msg">
                        <?= htmlspecialchars($form->getError('email')) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">*Password</label>
                <input type="password" id="password" name="password" value="<?= $form->getValue('password') ?>">
                <?php if ($form->getError('password')): ?>
                    <span class="error-msg">
                        <?= htmlspecialchars($form->getError('password')) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">*Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                    value="<?= $form->getValue('confirm_password') ?>">
                <?php if ($form->getError('confirm_password')): ?>
                    <span class="error-msg">
                        <?= htmlspecialchars($form->getError('confirm_password')) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn-register">Register</button>

    </form>

    <!-- ---- Records Table ---- -->
    <?php if (empty($records)): ?>
        <p class="no-records">No records to display.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($row['first_name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['last_name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['email']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['password']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>

</html>