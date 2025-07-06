<?php

echo "🔧 Checking and fixing User model...\n\n";

$userModelPath = 'app/Models/User.php';

if (!file_exists($userModelPath)) {
    echo "❌ User model not found at $userModelPath\n";
    exit;
}

$content = file_get_contents($userModelPath);

// Check if the OTP fields are in fillable
$needsUpdate = false;
$requiredFields = ['reset_otp', 'reset_otp_expires_at', 'reset_token', 'reset_token_expires_at'];

foreach ($requiredFields as $field) {
    if (strpos($content, "'$field'") === false) {
        echo "⚠️  Missing from fillable: $field\n";
        $needsUpdate = true;
    } else {
        echo "✅ Found in fillable: $field\n";
    }
}

if ($needsUpdate) {
    echo "\n🔧 The User model needs to be updated to include OTP fields in fillable array.\n";
    echo "Please check your app/Models/User.php file.\n";
} else {
    echo "\n✅ User model looks good!\n";
}

// Also check if the fields are in hidden array (they should be)
$hiddenFields = ['reset_otp', 'reset_token'];
foreach ($hiddenFields as $field) {
    if (strpos($content, "'$field'") !== false && strpos($content, 'hidden') !== false) {
        echo "✅ $field is properly hidden\n";
    } else {
        echo "⚠️  $field should be in hidden array\n";
    }
}
