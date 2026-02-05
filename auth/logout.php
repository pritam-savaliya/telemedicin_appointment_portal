<?php
session_start();
include '../includes/db.php';
session_destroy();
header("Location: " . BASE_URL . "index.php?msg=logged_out");
exit();
?>