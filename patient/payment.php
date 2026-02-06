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
    // Simulate Payment Processing based on Method
    $payment_method = $_POST['payment_method'] ?? 'unknown';

    // In a real app, you would verify with a gateway here.
    // For this simulation, we consider all submitted forms as "Paid".

    $patient_id = $_SESSION['user_id'];
    $doctor_id = $booking['doctor_id'];
    $date = $booking['date'];
    $time = $booking['time'];
    $status = 'confirmed'; // Auto-confirm after payment
    $payment_status = 'paid';

    // Generate simulated Transaction ID
    $prefix = 'TXN';
    if ($payment_method == 'card')
        $prefix = 'CARD';
    elseif ($payment_method == 'upi')
        $prefix = 'UPI';
    elseif ($payment_method == 'bhim')
        $prefix = 'BHIM';

    $transaction_id = $prefix . strtoupper(uniqid());

    // 1. Insert Appointment
    $sql_apt = "INSERT INTO appointments (patient_id, doctor_id, date, time, status, payment_status) 
                VALUES ('$patient_id', '$doctor_id', '$date', '$time', '$status', '$payment_status')";

    if ($conn->query($sql_apt) === TRUE) {
        $appointment_id = $conn->insert_id;

        // 2. Insert Payment Record
        $sql_pay = "INSERT INTO payments (user_id, appointment_id, amount, transaction_id, status) 
                    VALUES ('$patient_id', '$appointment_id', '$amount', '$transaction_id', 'success')";
        $conn->query($sql_pay);

        // 3. Send Email Receipt
        $to = $_SESSION['email'];
        $subject = "Payment Receipt - TeleMed";
        $message = "
        <html>
        <head>
          <title>Payment Receipt</title>
        </head>
        <body style='font-family: Arial, sans-serif;'>
          <h2 style='color: #2ecc71;'>Payment Successful</h2>
          <p>Dear " . explode(' ', $_SESSION['fullname'])[0] . ",</p>
          <p>Thank you for your payment. Your appointment has been confirmed.</p>
          <table border='0' cellpadding='10' cellspacing='0' style='background: #f9f9f9; width: 100%; max-width: 600px; border-radius: 8px;'>
            <tr>
                <td style='border-bottom: 1px solid #eee;'><strong>Transaction ID</strong></td>
                <td style='border-bottom: 1px solid #eee;'>$transaction_id</td>
            </tr>
            <tr>
                <td style='border-bottom: 1px solid #eee;'><strong>Doctor</strong></td>
                <td style='border-bottom: 1px solid #eee;'>Dr. $doctor_name</td>
            </tr>
            <tr>
                <td style='border-bottom: 1px solid #eee;'><strong>Amount</strong></td>
                <td style='border-bottom: 1px solid #eee;'>₹$amount</td>
            </tr>
            <tr>
                <td><strong>Date & Time</strong></td>
                <td>$date at $time</td>
            </tr>
          </table>
          <p style='margin-top: 20px; color: #777; font-size: 0.9rem;'>Regards,<br>TeleMed Team</p>
        </body>
        </html>
        ";

        // Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@telemed.com" . "\r\n";

        // Send (suppressed warning)
        @mail($to, $subject, $message, $headers);

        // Log to file for localhost
        $log_entry = "To: $to | Subject: $subject | txn: $transaction_id\n";
        file_put_contents("../email_log.txt", $log_entry, FILE_APPEND);

        // 4. Clear Session
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
        background: var(--bg-card);
        padding: 30px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .payment-form {
        background: var(--bg-card);
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
    }

    .amount-display {
        background: var(--bg-section);
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--primary);
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

<!-- Success Animation Overlay -->
<div id="success-overlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
    <div class="checkmark-circle">
        <div class="background"></div>
        <div class="checkmark draw"></div>
    </div>
    <h2
        style="color: white; margin-top: 20px; text-align: center; font-family: var(--font-heading); opacity: 0; animation: fadeInUp 0.5s ease 0.5s forwards;">
        Payment Successful!</h2>
    <p style="color: rgba(255,255,255,0.8); margin-top: 10px; opacity: 0; animation: fadeInUp 0.5s ease 0.7s forwards;">
        Redirecting you securely...</p>
</div>

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

        <div class="payment-methods-container">
            <!-- Payment Tabs -->
            <div class="payment-tabs">
                <button class="tab-btn active" onclick="switchTab('card')"><i class="fas fa-credit-card"></i>
                    Card</button>
                <button class="tab-btn" onclick="switchTab('upi')"><i class="fas fa-mobile-alt"></i> UPI</button>
                <button class="tab-btn" onclick="switchTab('bhim')"><i class="fas fa-qrcode"></i> BHIM</button>
            </div>

            <!-- Card Payment Form -->
            <form action="" method="POST" id="card-form" class="payment-content active">
                <input type="hidden" name="payment_method" value="card">

                <div class="form-group">
                    <label>Card Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-credit-card"></i>
                        <input type="text" name="card_number" class="form-control" placeholder="0000 0000 0000 0000"
                            maxlength="19" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Cardholder Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="card_name" class="form-control" placeholder="Name on Card" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-half">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" name="expiry" class="form-control" placeholder="MM/YY" maxlength="5"
                                required>
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label>CVV</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="cvv" class="form-control" placeholder="123" maxlength="3"
                                    required>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Pay ₹<?php echo $amount; ?>
                </button>
            </form>

            <!-- UPI Payment Form -->
            <form action="" method="POST" id="upi-form" class="payment-content">
                <input type="hidden" name="payment_method" value="upi">

                <div class="text-center mb-4">
                    <img src="../assets/img/upi-logo.png" alt="UPI"
                        style="height: 40px; margin-bottom: 20px; opacity: 0.8;">
                    <p class="text-muted">Enter your UPI ID to pay securely</p>
                </div>

                <div class="form-group">
                    <label>UPI ID</label>
                    <div class="input-with-icon">
                        <i class="fas fa-at"></i>
                        <input type="text" name="upi_id" class="form-control" placeholder="username@bank"
                            pattern="^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$"
                            title="Please enter a valid UPI ID (e.g. name@okicici, 9876543210@upi)" id="upi_id_input">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Verify & Pay ₹<?php echo $amount; ?>
                </button>
            </form>

            <!-- BHIM / QR Form -->
            <form action="" method="POST" id="bhim-form" class="payment-content">
                <input type="hidden" name="payment_method" value="bhim">

                <div style="text-align: center; padding: 20px;">
                    <h5 style="margin-bottom: 15px;">Scan QR Code</h5>
                    <div
                        style="background: white; padding: 20px; display: inline-block; border-radius: 10px; border: 1px solid var(--border-color);">

                        <?php
                        // Construct the Raw UPI String
                        $upi_data = "upi://pay?pa=telemed@business&pn=TeleMed&am=" . $amount . "&cu=INR";
                        // URL Encode it for the QR API
                        $encoded_upi = urlencode($upi_data);
                        ?>

                        <!-- Placeholder QR Code Generator with Properly Encoded Data -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $encoded_upi; ?>"
                            alt="QR Code">
                    </div>

                    <div id="bhim-status"
                        style="margin-top: 25px; padding: 15px; background: var(--bg-section); border-radius: 8px;">
                        <p style="margin-bottom: 10px; font-weight: bold;">Step 1: Scan & Pay ₹<?php echo $amount; ?>
                        </p>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">
                            <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Waiting for payment
                            confirmation...
                        </div>
                        <p style="margin-top: 10px; font-size: 0.8rem; color: var(--text-muted);">
                            Please do not close this window.
                        </p>
                    </div>
                </div>

                <!-- Hidden input -->
                <input type="hidden" name="bhim_confirmed" value="true">
            </form>
        </div>
    </div>
</div>

<style>
    /* ... existing styles ... */

    /* Tabs Styling */
    .payment-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        background: var(--bg-section);
        padding: 5px;
        border-radius: 12px;
    }

    .tab-btn {
        flex: 1;
        padding: 12px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .tab-btn.active {
        background: var(--bg-card);
        color: var(--primary);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .tab-btn:hover:not(.active) {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text-main);
    }

    /* Content Styling */
    .payment-content {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .payment-content.active {
        display: block;
    }

    /* Input Icons */
    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .input-with-icon input {
        padding-left: 45px;
    }

    .row {
        display: flex;
        gap: 20px;
    }

    .col-half {
        flex: 1;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Checkmark Animation */
    .checkmark-circle {
        width: 100px;
        height: 100px;
        position: relative;
        display: inline-block;
        vertical-align: top;
    }

    .checkmark-circle .background {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #2ECC71;
        position: absolute;
    }

    .checkmark-circle .checkmark {
        border-radius: 5px;
    }

    .checkmark-circle .checkmark.draw:after {
        animation-delay: 100ms;
        animation-duration: 1s;
        animation-timing-function: ease;
        animation-name: checkmark;
        transform: scaleX(-1) rotate(135deg);
        animation-fill-mode: forwards;
    }

    .checkmark-circle .checkmark:after {
        opacity: 0;
        height: 0;
        width: 0;
        transform-origin: left top;
        border-right: 15px solid white;
        border-top: 15px solid white;
        content: '';
        left: 25px;
        top: 50px;
        position: absolute;
    }

    @keyframes checkmark {
        0% {
            height: 0;
            width: 0;
            opacity: 0;
        }

        20% {
            height: 0;
            width: 25px;
            opacity: 1;
        }

        40% {
            height: 50px;
            width: 25px;
            opacity: 1;
        }

        100% {
            height: 50px;
            width: 25px;
            opacity: 1;
        }
    }
</style>

<script>
    let bhimTimer = null;

    function switchTab(method) {
        // Toggle Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        // event.currentTarget might be null if called programmatically, ensure it exists
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        } else {
            // Fallback if event is not available (e.g., initial load)
            const btn = document.querySelector(`.tab-btn[onclick*="${method}"]`);
            if (btn) btn.classList.add('active');
        }

        // Toggle Content
        document.querySelectorAll('.payment-content').forEach(content => content.classList.remove('active'));
        document.getElementById(method + '-form').classList.add('active');

        // Manage Required Fields
        document.querySelectorAll('form').forEach(f => {
            if (f.id === method + '-form') {
                toggleRequired(f, true);
            } else {
                toggleRequired(f, false);
            }
        });

        // BHIM Automatic Simulation Logic
        // Clear previous timer if exists
        if (bhimTimer) {
            clearTimeout(bhimTimer);
            bhimTimer = null;
            // Reset wording if needed (optional)
        }

        if (method === 'bhim') {
            startBhimSimulation();
        }
    }

    function startBhimSimulation() {
        const container = document.getElementById('bhim-status');
        // Reset Text
        container.innerHTML = `
            <p style="margin-bottom: 10px; font-weight: bold;">Step 1: Scan & Pay ₹500</p>
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Waiting for payment confirmation...
            </div>
            <p style="margin-top: 10px; font-size: 0.8rem; color: var(--text-muted);">
                Please do not close this window.
            </p>
        `;

        // Wait 8 seconds to simulate user scanning and paying
        bhimTimer = setTimeout(() => {
            container.innerHTML = `
                <div style="color: var(--success); font-weight: bold; font-size: 1.1rem; animation: fadeInUp 0.5s;">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Payment Received!
                </div>
                <p style="margin-top: 5px; font-size: 0.9rem;">Verifying...</p>
            `;

            // Wait another 1.5 seconds then auto-submit
            setTimeout(() => {
                const form = document.getElementById('bhim-form');
                if (form.classList.contains('active')) {
                    // Send
                    // Create and dispatch submit event to trigger the main submit listener (animation)
                    const event = new Event('submit', {
                        'bubbles': true,
                        'cancelable': true
                    });
                    form.dispatchEvent(event);
                }
            }, 1500);

        }, 8000);
    }

    function toggleRequired(form, isRequired) {
        const inputs = form.querySelectorAll('input:not([type="hidden"])');
        inputs.forEach(input => {
            if (isRequired) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    // Initialize Card Form as required
    document.addEventListener('DOMContentLoaded', () => {
        toggleRequired(document.getElementById('upi-form'), false);
        toggleRequired(document.getElementById('bhim-form'), false);
        // Ensure card form is active and its fields are required on initial load
        switchTab('card');
    });

    // Card Input Formatting (Spaces & Slash)
    document.addEventListener('DOMContentLoaded', () => {
        const cardNumInput = document.querySelector('input[name="card_number"]');
        const expiryInput = document.querySelector('input[name="expiry"]');
        const cvvInput = document.querySelector('input[name="cvv"]');

        if (cardNumInput) {
            cardNumInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                if (value.length > 16) value = value.slice(0, 16); // Limit to 16 digits
                // Add spaces every 4 digits
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
        }

        if (expiryInput) {
            expiryInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                // Month (2) + Year (2)
                if (value.length > 4) value = value.slice(0, 4);

                if (value.length >= 2) {
                    e.target.value = value.slice(0, 2) + '/' + value.slice(2);
                } else {
                    e.target.value = value;
                }
            });
        }

        if (cvvInput) {
            cvvInput.addEventListener('input', function (e) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
            });
        }

        // UPI Validation
        const upiInput = document.getElementById('upi_id_input');
        const upiRegex = /^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$/;

        if (upiInput) {
            upiInput.addEventListener('input', function (e) {
                const val = e.target.value;
                if (val.length === 0) {
                    e.target.style.borderColor = 'var(--border-color)';
                    e.target.style.background = '';
                    return;
                }

                if (upiRegex.test(val)) {
                    e.target.style.borderColor = '#2ecc71'; // Success Green
                    e.target.style.backgroundColor = 'rgba(46, 204, 113, 0.05)';
                } else {
                    e.target.style.borderColor = '#e74c3c'; // Error Red
                    e.target.style.backgroundColor = '';
                }
            });
        }
    });

    // Handle Form Submission with Animation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            // Only prevent default if the form is currently active
            if (this.classList.contains('active')) {
                e.preventDefault(); // Stop default submission

                // Check form validity before showing animation
                if (!this.checkValidity()) {
                    this.reportValidity();
                    return;
                }

                // Show Animation
                const overlay = document.getElementById('success-overlay');
                overlay.style.display = 'flex';

                // Allow animation to play for 2.5 seconds, then execute actual submit
                setTimeout(() => {
                    const tempForm = document.createElement('form');
                    tempForm.method = this.method;
                    tempForm.action = this.action;
                    tempForm.style.display = 'none';

                    Array.from(this.elements).forEach(element => {
                        if (element.name && !element.disabled) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = element.name;
                            input.value = element.value;
                            tempForm.appendChild(input);
                        }
                    });

                    document.body.appendChild(tempForm);
                    tempForm.submit();
                }, 2500);
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
```