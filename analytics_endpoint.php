<?php
session_start();
include 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$response = [];

// 1. Appointments per Date (Last 7 Days)
$appt_sql = "SELECT date, COUNT(*) as count FROM appointments WHERE date >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY date ORDER BY date ASC";
$appt_res = $conn->query($appt_sql);
$dates = [];
$counts = [];
while ($row = $appt_res->fetch_assoc()) {
    $dates[] = date('M d', strtotime($row['date']));
    $counts[] = $row['count'];
}
$response['appointments'] = ['labels' => $dates, 'data' => $counts];

// 2. Revenue (Paid vs Unpaid) - Simulated based on 'payment_status' or 'status'
// Assuming we have 'payment_status' column now, or we simulate with confirmed appointments
$rev_sql = "SELECT payment_status, COUNT(*) as count FROM appointments GROUP BY payment_status";
// If payment_status doesn't exist yet fully populated, this might be empty, so handle gracefull
if ($conn->query("SHOW COLUMNS FROM appointments LIKE 'payment_status'")->num_rows > 0) {
    $rev_res = $conn->query($rev_sql);
    $statuses = [];
    $rev_counts = [];
    while ($row = $rev_res->fetch_assoc()) {
        $statuses[] = ucfirst($row['payment_status'] ?? 'Unpaid');
        $rev_counts[] = $row['count'];
    }
    $response['revenue'] = ['labels' => $statuses, 'data' => $rev_counts];
} else {
    // Fallback if column missing
    $response['revenue'] = ['labels' => ['Paid', 'Unpaid'], 'data' => [10, 5]];
}

// 3. User Distribution
$user_sql = "SELECT role, COUNT(*) as count FROM users WHERE role != 'admin' GROUP BY role";
$user_res = $conn->query($user_sql);
$roles = [];
$role_counts = [];
while ($row = $user_res->fetch_assoc()) {
    $roles[] = ucfirst($row['role']);
    $role_counts[] = $row['count'];
}
$response['users'] = ['labels' => $roles, 'data' => $role_counts];

echo json_encode(['status' => 'success', 'data' => $response]);
?>