<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['appointment_id'])) {
    header("Location: home.php");
    exit();
}

$appointment_id = $_GET['appointment_id'];
$user_id = $_SESSION['user_id'];

// Verify access
$check_sql = "SELECT appointments.*, 
              (SELECT fullname FROM users WHERE id = appointments.doctor_id) as doctor_name,
              (SELECT fullname FROM users WHERE id = appointments.patient_id) as patient_name
              FROM appointments 
              WHERE id = $appointment_id AND (patient_id = $user_id OR doctor_id = $user_id) AND status = 'confirmed'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows == 0) {
    die("Access Denied: Appointment not confirmed or you are not authorized.");
}

$appointment = $check_result->fetch_assoc();
$room_name = "TelemedCall_" . $appointment_id . "_" . md5($appointment['date'] . "SecretSalt");
$user_name = $_SESSION['fullname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Consultation - Telemedicine</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: #000;
        }

        #meet {
            width: 100%;
            height: 100%;
        }

        .video-header {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            height: 70px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 10;
            color: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .consultation-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-end-call {
            background: #ff4757;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.4);
        }

        .btn-end-call:hover {
            transform: translateY(-2px);
            background: #ff6b81;
            box-shadow: 0 6px 20px rgba(255, 71, 87, 0.6);
        }
    </style>
</head>

<body>

    <div class="video-header">
        <div class="consultation-info">
            <div
                style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-video"></i>
            </div>
            <div>
                Consultation with
                <span style="color: var(--accent-color);">
                    <?php echo ($_SESSION['role'] == 'patient') ? 'Dr. ' . $appointment['doctor_name'] : $appointment['patient_name']; ?>
                </span>
            </div>
        </div>

        <?php if ($_SESSION['role'] == 'doctor'): ?>
            <button onclick="endCall()" class="btn-end-call">
                <i class="fas fa-phone-slash"></i> End Call
            </button>
        <?php else: ?>
            <a href="patient_dashboard.php" class="btn-end-call">
                <i class="fas fa-sign-out-alt"></i> Leave Call
            </a>
        <?php endif; ?>
    </div>

    <div id="meet"></div>

    <script src='https://meet.jit.si/external_api.js'></script>
    <script>
        const appointmentId = <?php echo $appointment_id; ?>;

        function endCall() {
            if (confirm("Are you sure you want to end this consultation? This will close the room for the patient too.")) {
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);
                formData.append('status', 0); // Set inactive

                fetch('toggle_call_status.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        window.location.href = 'doctor_dashboard.php';
                    });
            }
        }
        const domain = 'meet.jit.si';
        const options = {
            roomName: '<?php echo $room_name; ?>',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#meet'),
            userInfo: {
                displayName: '<?php echo $user_name; ?>'
            },
            configOverwrite: {
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                disableDeepLinking: true
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'chat', 'raisehand',
                    'videoquality', 'filmstrip', 'tileview', 'videobackgroundblur'
                ],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>

</body>

</html>