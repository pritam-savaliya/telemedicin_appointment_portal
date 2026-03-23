<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
if ($appointment_id <= 0) {
    header("Location: my_prescriptions.php");
    exit();
}

$sql = "SELECT p.*, u.fullname as doctor_name, u.email as doctor_email, pat.fullname as patient_name, pat.email as patient_email, a.date
        FROM prescriptions p
        JOIN users u ON p.doctor_id = u.id
        JOIN users pat ON p.patient_id = pat.id
        JOIN appointments a ON p.appointment_id = a.id
        WHERE p.appointment_id = $appointment_id";

$result = $conn->query($sql);
if ($result->num_rows == 0) {
    include '../includes/header.php'; 
    echo '<div class="container section" style="text-align: center; padding: 100px 0;">
            <div class="glass-card" style="max-width: 600px; margin: 0 auto; padding: 4rem;">
                <i class="fas fa-prescription-bottle-medical" style="font-size: 4rem; color: var(--warning); margin-bottom: 2rem; opacity: 0.5;"></i>
                <h2 style="margin-bottom: 1rem;">Prescription Not Found</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">It seems a prescription hasn\'t been issued for this appointment yet. Please contact your doctor.</p>
                <a href="my_appointments.php" class="btn btn-primary">Back to History</a>
            </div>
          </div>';
    include '../includes/footer.php';
    exit();
}


$pres = $result->fetch_assoc();

// Security: Check if user is the patient or the doctor
if ($_SESSION['role'] == 'patient' && $pres['patient_id'] != $_SESSION['user_id']) {
    include '../includes/header.php'; 
    echo '<div class="container section" style="text-align: center; padding: 100px 0;">
            <div class="glass-card" style="max-width: 600px; margin: 0 auto; padding: 4rem;">
                <i class="fas fa-user-shield" style="font-size: 4rem; color: var(--accent); margin-bottom: 2rem; opacity: 0.5;"></i>
                <h2 style="margin-bottom: 1rem;">Access Denied</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">You do not have permission to view this prescription.</p>
                <a href="my_appointments.php" class="btn btn-primary">Back to History</a>
            </div>
          </div>';
    include '../includes/footer.php';
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - #<?php echo $pres['id']; ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=Merriweather:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0984e3;
            --secondary-color: #2d3436;
            --accent-color: #00b894;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #dfe6e9;
            padding: 40px 0;
            margin: 0;
            color: var(--secondary-color);
        }

        .prescription-paper {
            background: white;
            width: 210mm;
            /* A4 width */
            min-height: 297mm;
            /* A4 height */
            margin: 0 auto;
            padding: 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Decorative Header Bar */
        .top-bar {
            height: 15px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            width: 100%;
        }

        .content-wrapper {
            padding: 50px;
            flex: 1;
            position: relative;
            z-index: 2;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 400px;
            color: rgba(9, 132, 227, 0.03);
            pointer-events: none;
            z-index: 1;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
            border-bottom: 2px solid #f1f2f6;
            padding-bottom: 30px;
        }

        .doctor-info h2 {
            margin: 0 0 5px 0;
            color: var(--primary-color);
            font-family: 'Merriweather', serif;
            font-size: 1.8rem;
        }

        .doctor-info p {
            margin: 2px 0;
            color: #636e72;
            font-size: 0.95rem;
        }

        .brand-logo {
            text-align: right;
        }

        .brand-logo .icon {
            font-size: 2.5rem;
            color: var(--accent-color);
        }

        .brand-logo .text {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary-color);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 20px;
            background: var(--light-bg);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 40px;
            border: 1px solid #e1e1e1;
        }

        .meta-item label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #b2bec3;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .meta-item strong {
            font-size: 1.1rem;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .rx-symbol {
            font-family: 'Merriweather', serif;
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: inline-block;
            font-style: italic;
        }

        .rx-content {
            font-family: 'Merriweather', serif;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #2d3436;
            margin-bottom: 40px;
            padding-left: 20px;
            border-left: 4px solid #f1f2f6;
        }

        .notes-section {
            background: #fff8e1;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffce00;
            margin-top: 20px;
        }

        .notes-section h4 {
            margin: 0 0 10px 0;
            color: #d35400;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .notes-section p {
            margin: 0;
            font-size: 0.95rem;
            color: #636e72;
        }

        .footer {
            margin-top: auto;
            border-top: 1px solid #f1f2f6;
            padding-top: 30px;
            padding: 40px 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            background: #fff;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }

        .generated-info {
            font-size: 0.8rem;
            color: #b2bec3;
            text-align: right;
        }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 16px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(9, 132, 227, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 100;
        }

        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(9, 132, 227, 0.4);
        }

        @media print {
            body {
                background: white;
                padding: 0;
                -webkit-print-color-adjust: exact;
            }

            .prescription-paper {
                width: 100%;
                min-height: 100vh;
                box-shadow: none;
                margin: 0;
            }

            .print-btn {
                display: none;
            }

            .content-wrapper {
                padding: 40px;
            }
        }
    </style>
</head>

<body>

    <div class="prescription-paper">
        <div class="top-bar"></div>
        <i class="fas fa-file-medical watermark"></i>

        <div class="content-wrapper">
            <!-- Header -->
            <div class="header">
                <div class="doctor-info">
                    <h2>Dr. <?php echo $pres['doctor_name']; ?></h2>
                    <p
                        style="text-transform: uppercase; font-size: 0.8rem; font-weight: 600; color: var(--accent-color);">
                        Certified Medical Specialist</p>
                    <p>MBBS, MD (General Medicine)</p>
                    <p><i class="fas fa-envelope-open-text" style="font-size: 0.8rem;"></i>
                        <?php echo $pres['doctor_email']; ?></p>
                </div>
                <div class="brand-logo">
                    <div class="icon"><i class="fas fa-heartbeat"></i></div>
                    <div class="text">TeleMed</div>
                    <div style="font-size: 0.7rem; color: #b2bec3;">Health Services</div>
                </div>
            </div>

            <!-- Patient Meta -->
            <div class="meta-grid">
                <div class="meta-item">
                    <label>Patient Name</label>
                    <strong><?php echo $pres['patient_name']; ?></strong>
                    <div style="font-size: 0.85rem; color: #636e72; margin-top: 2px;">
                        <?php echo $pres['patient_email']; ?></div>
                </div>
                <div class="meta-item">
                    <label>Prescription ID</label>
                    <strong>#<?php echo str_pad($pres['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                </div>
                <div class="meta-item">
                    <label>Date</label>
                    <strong><?php echo date("d M Y", strtotime($pres['created_at'])); ?></strong>
                </div>
                <div class="meta-item"
                    style="grid-column: 1 / -1; border-top: 1px dashed #dcdde1; padding-top: 15px; margin-top: 5px;">
                    <label>Diagnosis</label>
                    <strong
                        style="color: var(--secondary-color); font-family: 'Merriweather', serif;"><?php echo $pres['diagnosis']; ?></strong>
                </div>
            </div>

            <!-- Rx Section -->
            <div class="rx-symbol">Rx</div>
            <div class="rx-content">
                <?php echo nl2br($pres['prescription_text']); ?>
            </div>

            <!-- Notes -->
            <?php if (!empty($pres['notes'])): ?>
                <div class="notes-section">
                    <h4><i class="fas fa-info-circle"></i> Advice / Instructions</h4>
                    <p><?php echo nl2br($pres['notes']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="generated-info">
                This document is electronically generated by TeleMed.<br>
                Valid without physical signature.
            </div>
            <div class="signature-box">
                <img src="assets/img/signature-placeholder.png" alt="Dr. Signature"
                    style="height: 60px; opacity: 0.7; margin-bottom: 5px;">
                <div class="signature-line"></div>
                <div style="font-size: 0.9rem; font-weight: 600;">Dr. <?php echo $pres['doctor_name']; ?></div>
                <div style="font-size: 0.7rem; color: #b2bec3;">Authorized Medical Officer</div>
            </div>
        </div>
    </div>

    <a href="#" onclick="window.print()" class="print-btn">
        <i class="fas fa-print"></i> Print Document
    </a>

</body>

</html>
