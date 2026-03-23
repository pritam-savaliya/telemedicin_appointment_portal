<?php
session_start();

// Generate a new arithmetic CAPTCHA
$n1 = rand(1, 9);
$n2 = rand(1, 9);
$ans = $n1 + $n2;

// Store in session
$_SESSION['captcha_n1'] = $n1;
$_SESSION['captcha_n2'] = $n2;
$_SESSION['captcha_ans'] = $ans;

// Return JSON
header('Content-Type: application/json');
echo json_encode(['n1' => $n1, 'n2' => $n2]);
?>
