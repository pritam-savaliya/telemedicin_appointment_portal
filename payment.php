<?php
session_start();
include 'includes/db.php';

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

<?php include 'includes/header.php'; ?>

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

        <!-- Payment Form -->
        <div class="payment-form">
            <div class="secure-badge">
                <i class="fas fa-lock"></i> 100% Secure Transaction
            </div>

            <div class="card-preview">
                <div class="chip"></div>
                <div style="font-size: 1.2rem; letter-spacing: 2px; margin-bottom: 15px;">**** **** **** 4242</div>
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                    <span>CARD HOLDER</span>
                    <span>EXPIRES</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 600;">
                    <span>
                        <?php echo strtoupper($_SESSION['fullname']); ?>
                    </span>
                    <span>12/28</span>
                </div>
            </div>

            <form action="" method="POST">

                <div class="form-group">
                    <label>Data Card Holder Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" placeholder="John Doe"
                            value="<?php echo $_SESSION['fullname']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Card Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-credit-card"></i>
                        <input type="text" name="card_number" id="card_number" class="form-control"
                            placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <div class="input-with-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="text" id="expiry_date" class="form-control" placeholder="MM/YY" maxlength="5"
                                required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 15px; font-size: 1.1rem; margin-top: 10px;">
                    Pay ₹<?php echo $amount; ?> & Confirm
                </button>

                <p style="text-align: center; margin-top: 15px; font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fas fa-info-circle"></i> This is a simulated payment. No real money will be deducted.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cardInput = document.getElementById('card_number');
        const expiryInput = document.getElementById('expiry_date');

        // Card Number Formatting
        cardInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            let formattedValue = '';

            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }

            e.target.value = formattedValue;
        });

        // Expiry Date Formatting
        expiryInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits

            if (value.length >= 2) {
                e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                e.target.value = value;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>