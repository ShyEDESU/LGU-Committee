<?php
/**
 * Verification script for email validation logic
 */

require_once 'public/pages/user-management/user_functions.php';

function test_validation($email, $userId = null)
{
    $data = [
        'email' => $email,
        'user_id' => $userId,
        'first_name' => 'Test',
        'last_name' => 'User',
        'role_name' => 'User'
    ];

    $errors = validateUserData($data, $userId !== null);

    echo "Testing email: $email" . ($userId ? " (Update for ID $userId)" : " (Create)") . "\n";
    if (empty($errors)) {
        echo "RESULT: PASS (Valid)\n";
    } else {
        echo "RESULT: FAIL\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    echo "---------------------------------\n";
}

echo "Starting Email Validation Tests...\n\n";

// 1. Test existing email (from schema)
test_validation('LGU@admin.com');

// 2. Test invalid domain
test_validation('test@invalid.domain.xyz');

// 3. Test valid non-existent email
test_validation('nobody_123456789@gmail.com');

// 4. Test update with same email (should pass)
test_validation('LGU@admin.com', 2); // Assuming 2 is LGU Admin ID

// 5. Test update with other existing email (should fail)
test_validation('super.admin@legislative-services.gov', 2);

echo "\nVerification Complete.\n";
