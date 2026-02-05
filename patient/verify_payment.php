<?php
session_start();
include '../includes/db.php';
require_once '../includes/razorpay_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['booking_details'])) {

    $success = true;
    $error = "Payment Failed";

    if (empty($_POST['razorpay_payment_id']) === false) {

        $api_key = RAZORPAY_KEY_ID;
        $api_secret = RAZORPAY_KEY_SECRET;

        $razorpay_payment_id = $_POST['razorpay_payment_id'];
        $razorpay_order_id = $_POST['razorpay_order_id'];
        $razorpay_signature = $_POST['razorpay_signature'];

        // Signature Verification
        $generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $api_secret);

        if ($generated_signature == $razorpay_signature) {

            // Payment Success Logic
            $booking = $_SESSION['booking_details'];
            $patient_id = $_SESSION['user_id'];
            $doctor_id = $booking['doctor_id'];
            $date = $booking['date'];
            $time = $booking['time'];
            $amount = 500; // Fixed Amount

            // 1. Insert Appointment
            $sql_apt = "INSERT INTO appointments (patient_id, doctor_id, date, time, status, payment_status) 
                        VALUES ('$patient_id', '$doctor_id', '$date', '$time', 'confirmed', 'paid')";

            if ($conn->query($sql_apt) === TRUE) {
                $appointment_id = $conn->insert_id;

                // 2. Insert Payment Record
                $sql_pay = "INSERT INTO payments (user_id, appointment_id, amount, transaction_id, status) 
                            VALUES ('$patient_id', '$appointment_id', '$amount', '$razorpay_payment_id', 'success')";
                $conn->query($sql_pay);

                // 3. Send Emails
                include_once '../includes/smtp_config.php';

                // Fetch Doctor Details
                $doc_res = $conn->query("SELECT fullname, email FROM users WHERE id = '$doctor_id'");
                $doc_row = $doc_res->fetch_assoc();
                $doctor_email = $doc_row['email'];
                $doctor_name = $doc_row['fullname'];

                $patient_email = $_SESSION['email'];
                $patient_name = $_SESSION['fullname'];

                // Email to Patient
                $subject_pat = "Appointment Confirmed - TeleMed";
                $body_pat = "Hi $patient_name,<br><br>Your appointment with Dr. $doctor_name has been confirmed for <b>$date at $time</b>.<br>Transaction ID: $razorpay_payment_id<br><br>Thank you for choosing TeleMed.";
                sendEmail($patient_email, $subject_pat, $body_pat);

                // Email to Doctor
                $subject_doc = "New Appointment Booked";
                $body_doc = "Hi Dr. $doctor_name,<br><br>You have a new appointment with $patient_name on <b>$date at $time</b>.<br>Please login to your dashboard to view details.";
                sendEmail($doctor_email, $subject_doc, $body_doc);

                // 4. Clear Session
                unset($_SESSION['booking_details']);

                echo json_encode(['status' => 'success']);
                exit();
            } else {
                $success = false;
                $error = "Database Error: " . $conn->error;
            }

        } else {
            $success = false;
            $error = "Invalid Signature";
        }
    } else {
        $success = false;
        $error = "Payment ID missing";
    }

    echo json_encode(['status' => 'error', 'message' => $error]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>
