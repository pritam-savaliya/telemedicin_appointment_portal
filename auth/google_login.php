<?php
require_once '../includes/google_config.php';
header('Location: ' . $google_oauth_url);
exit();
?>
