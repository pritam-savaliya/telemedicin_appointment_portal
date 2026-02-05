<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'fetch') {
    // 1. Get Unread Count
    $count_sql = "SELECT COUNT(*) as c FROM notifications WHERE user_id = $user_id AND is_read = 0";
    $count_res = $conn->query($count_sql);
    $unread_count = $count_res->fetch_assoc()['c'];

    // 2. Get Latest Notifications (Limit 5)
    $sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($sql);

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        // Pretty Date
        $time = strtotime($row['created_at']);
        $row['time_ago'] = humanTiming($time) . ' ago';
        $notifications[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'count' => $unread_count,
        'notifications' => $notifications
    ]);

} elseif ($action == 'mark_read') {
    $notif_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($notif_id > 0) {
        $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $user_id");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }

} elseif ($action == 'mark_all_read') {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    echo json_encode(['status' => 'success']);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

// Helper function for "5 mins ago"
function humanTiming($time)
{
    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit)
            continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }
}
?>
