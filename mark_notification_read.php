<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

if (isset($_POST['id'])) {
    $notif_id = $_POST['id'];
    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = $notif_id AND user_id = $user_id";
    $conn->query($sql);
}
?>