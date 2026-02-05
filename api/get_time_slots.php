<?php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$doctor_id = intval($_GET['doctor_id']);
$date = $_GET['date'];
$day_of_week = date('l', strtotime($date));

// 1. Get Doctor's Schedule for this day
$sched_sql = "SELECT * FROM doctor_schedules WHERE doctor_id = $doctor_id AND day_of_week = '$day_of_week'";
$sched_res = $conn->query($sched_sql);

if ($sched_res->num_rows == 0) {
    echo json_encode(['available' => false, 'message' => 'Doctor has no schedule for ' . $day_of_week]);
    exit();
}

$schedule = $sched_res->fetch_assoc();

if (!$schedule['is_available']) {
    echo json_encode(['available' => false, 'message' => 'Doctor is unavailable on ' . $day_of_week]);
    exit();
}

$start_time = strtotime($schedule['start_time']);
$end_time = strtotime($schedule['end_time']);

// 2. Get Existing Appointments
$appt_sql = "SELECT time FROM appointments WHERE doctor_id = $doctor_id AND date = '$date' AND status != 'rejected' AND status != 'cancelled'";
$appt_res = $conn->query($appt_sql);
$booked_slots = [];
while ($row = $appt_res->fetch_assoc()) {
    $booked_slots[] = date('H:i:s', strtotime($row['time']));
}

// 3. Generate Slots (30 min intervals)
$slots = [];
$current = $start_time;

while ($current < $end_time) {
    $slot_time = date('H:i:s', $current);
    $display_time = date('h:i A', $current);

    // Check if slot is already booked (Logic: simple exact match)
    // Note: Improved conflict logic might be needed for overlapping ranges, but fixed slots are safer.
    if (!in_array($slot_time, $booked_slots)) {
        $slots[] = [
            'value' => $slot_time,
            'display' => $display_time
        ];
    }

    $current = strtotime('+30 minutes', $current);
}

echo json_encode(['available' => true, 'slots' => $slots]);
