<?php
// config/email_config.php

// PHPMailer paths
define('PHPMAILER_PATH', __DIR__ . '/../phpmailer/src/');

// ⚠️ UPDATE THESE WITH YOUR DETAILS:

// Your Gmail address
define('SMTP_USERNAME', 'rhonjames95@gmail.com'); // Example: johnsmith@gmail.com

// Your 16-character App Password (generated in Step 5)
define('SMTP_PASSWORD', 'zunt xxuo kngi ehiu'); // Copy from Gmail

// Email that appears as sender
define('SMTP_FROM_EMAIL', 'rhonjames95@gmail.com'); // Same as SMTP_USERNAME

// Name that appears as sender
define('SMTP_FROM_NAME', 'WMSU iElect System');

// Gmail SMTP settings (DO NOT CHANGE)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);

// Your website URL (where your system is hosted)
define('SYSTEM_URL', 'http://localhost/system'); // Or your actual domain

// Feature toggles
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_SYSTEM_NOTIFICATIONS', true);

// Debug mode (true = show errors, false = hide errors)
define('EMAIL_DEBUG', true); // Set to false after testing
?>