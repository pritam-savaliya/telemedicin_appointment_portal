<?php
session_start();

// Generate a random 5-character string
$permitted_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$string_length = 5;
$captcha_string = '';

for ($i = 0; $i < $string_length; $i++) {
    $captcha_string .= $permitted_chars[mt_rand(0, strlen($permitted_chars) - 1)];
}

// Store in session
$_SESSION['captcha_code'] = $captcha_string;

// Return the code
echo $captcha_string;
?>