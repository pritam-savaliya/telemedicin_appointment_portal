<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['booking_details'])) {
    header("Location: book_appointment.php");
    exit();
}

$booking = $_SESSION['booking_details'];
$amount = 500; // Fixed Consultation Fee in Rupees

// Fetch Doctor Details for display
$doc_id = $booking['doctor_id'];
$doc_res = $conn->query("SELECT fullname FROM users WHERE id = $doc_id");
$doctor_name = $doc_res->fetch_assoc()['fullname'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Simulate Payment Processing
    $card_number = $_POST['card_number'];

    // In a real app, verify payment here. 
    // For now, assume success.

    $patient_id = $_SESSION['user_id'];
    $doctor_id = $booking['doctor_id'];
    $date = $booking['date'];
    $time = $booking['time'];
    $status = 'confirmed'; // Auto-confirm after payment
    $payment_status = 'paid';
    $transaction_id = 'TXN' . strtoupper(uniqid());

    // 1. Insert Appointment
    $sql_apt = "INSERT INTO appointments (patient_id, doctor_id, date, time, status, payment_status) 
                VALUES ('$patient_id', '$doctor_id', '$date', '$time', '$status', '$payment_status')";

    if ($conn->query($sql_apt) === TRUE) {
        $appointment_id = $conn->insert_id;

        // 2. Insert Payment Record
        $sql_pay = "INSERT INTO payments (user_id, appointment_id, amount, transaction_id, status) 
                    VALUES ('$patient_id', '$appointment_id', '$amount', '$transaction_id', 'success')";
        $conn->query($sql_pay);

        // 3. Clear Session
        unset($_SESSION['booking_details']);

        // 4. Redirect
        header("Location: patient_dashboard.php?msg=appointment_booked");
        exit();
    } else {
        $error = "Error booking appointment: " . $conn->error;
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .payment-container {
        max-width: 900px;
        margin: 40px auto;
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 40px;
    }

    @media (max-width: 768px) {
        .payment-container {
            grid-template-columns: 1fr;
        }
    }

    .order-summary {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 12px;
        height: h-100;
    }

    .payment-form {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--shadow-md);
        border: 1px solid #eee;
    }

    .amount-display {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--primary-color);
    }

    .card-preview {
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 25px;
        position: relative;
        overflow: hidden;
    }

    .card-preview .chip {
        width: 40px;
        height: 30px;
        background: #ffd700;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .secure-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--success-color);
        font-size: 0.9rem;
        margin-bottom: 20px;
        background: rgba(0, 184, 148, 0.1);
        padding: 10px;
        border-radius: 5px;
    }
</style>

<div class="container section">

    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Complete Your Booking</h2>
        <p style="color: var(--text-muted);">Secure Payment Gateway</p>
    </div>

    <div class="payment-container">
        <!-- Order Summary -->
        <div class="order-summary">
            <h4 style="margin-bottom: 20px;">Booking Summary</h4>

            <div style="margin-bottom: 15px;">
                <span style="display: block; color: var(--text-muted); font-size: 0.9rem;">Doctor</span>
                <strong style="font-size: 1.1rem;">Dr.
                    <?php echo $doctor_name; ?>
                </strong>
            </div>

            <div style="margin-bottom: 15px;">
                <span style="display: block; color: var(--text-muted); font-size: 0.9rem;">Date & Time</span>
                <strong>
                    <?php echo date('d M Y', strtotime($booking['date'])); ?>
                </strong> at <strong>
                    <?php echo date('h:i A', strtotime($booking['time'])); ?>
                </strong>
            </div>

            <div style="margin-bottom: 15px;">
                <span style="display: block; color: var(--text-muted); font-size: 0.9rem;">Consultation Type</span>
                <strong>Video Consultation</strong>
            </div>

            <hr style="border: 0; border-top: 1px solid #e9ecef; margin: 20px 0;">

            <div class="amount-display">
                <span>Total Amount</span>
                <span>₹
                    <?php echo $amount; ?>
                </span>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 30px;">
            <div class="secure-badge" style="justify-content: center; margin-bottom: 30px;">
                <i class="fas fa-lock"></i> Secured by Razorpay
            </div>

            <button id="pay-btn" class="btn btn-primary"
                style="width: 100%; padding: 15px; font-size: 1.1rem; background: #6c5ce7;">
                Pay ₹<?php echo $amount; ?> Now
            </button>

            <p style="margin-top: 15px; font-size: 0.9rem; color: var(--text-muted);">
                You will be redirected to the secure payment page.
            </p>
        </div>

        <form name='razorpayform' action="verify_payment.php" method="POST" id="razorpayform" style="display: none;">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        </form>
    </div>
</div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    // Create Order ID locally for demo purposes (In real app, create via API on backend)
    // For this implementation effectively, we rely on client-side init for simplicity in this demo environment
    // OR we can generate a random order ID placeholder if not using full backend Order API
    var orderId = "order_" + Math.random().toString(36).substr(2, 9); // Placeholder

    var options = {
        "key": "<?php include '../includes/razorpay_config.php';
        echo RAZORPAY_KEY_ID; ?>",
        "amount": "<?php echo $amount * 100; ?>", // Amount is in currency subunits. 50000 = 500 INR
        "currency": "INR",
        "name": "TeleMed",
        "description": "Consultation Fee",
        "image": "assets/img/logo.png", // Add your logo path if you have one
        "handler": function (response) {
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id || orderId; // Fallback for manual order
            document.getElementById('razorpay_signature').value = response.razorpay_signature;

            // Post to backend
            var formData = new FormData(document.getElementById('razorpayform'));
            // Since we don't have a real order ID from backend in this simple flow, we mock the signature check partially in verify_payment
            // or we just submit the payment ID to be verified.

            // For this specific 'test' flow without backend Order Creation API call:
            // We'll trust the payment_id for now or update verify_payment to be simpler.

            // To make this robust without backend Order API:
            // We will just send the payment ID to verify_payment.php 

            // AJAX submission
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "verify_payment.php", true);
            xhr.onload = function () {
                var res = JSON.parse(xhr.responseText);
                if (res.status == 'success') {
                    window.location.href = "patient_dashboard.php?msg=appointment_booked";
                } else {
                    alert(res.message);
                }
            };
            // Append manual data since verify_payment expects signature logic which requires Order ID
            // Let's simplified verify_payment to just check payment ID existence for this demo stage if needed, 
            // BUT to keep it 'real', let's stick to standard flow expectations. 
            // Note: Standard flow needs backend Order creation. 
            // workaround: We will pass the necessary data.
            formData.append('razorpay_order_id', response.razorpay_order_id);

            xhr.send(formData);
        },
        "prefill": {
            "name": "<?php echo $_SESSION['fullname']; ?>",
            "email": "<?php echo $_SESSION['email'] ?? 'test@example.com'; ?>", // Assuming email is in session or added
            "contact": "9999999999"
        },
        "theme": {
            "color": "#6c5ce7"
        }
    };

    document.getElementById('pay-btn').onclick = function (e) {
        var rzp1 = new Razorpay(options);
        rzp1.open();
        e.preventDefault();
    }
</script>

<?php include '../includes/footer.php'; ?>
