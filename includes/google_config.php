<?php
// Google API Configuration
// You can get these details from the Google Cloud Console: https://console.cloud.google.com/
define('GOOGLE_CLIENT_ID', '40381961554-4ocqsva3112nomrcmvls3i90v7oblkvg.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URL', 'http://localhost/telemedicine_appointment/auth/google_callback.php');

// Google OAuth URL Generation
$google_oauth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id' => '40381961554-4ocqsva3112nomrcmvls3i90v7oblkvg.apps.googleusercontent.com',
    'redirect_uri' => 'http://localhost/telemedicine_appointment/auth/google_callback.php',
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online'
]);
?>