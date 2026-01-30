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
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #meet {
            width: 100%;
            height: 100%;
        }

        .header-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-sizing: border-box;
            z-index: 10;
            color: white;
        }

        .btn-close {
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-close:hover {
            background: #ff6b81;
        }
    </style>
</head>

<body>

    <div class="header-bar">
        <div>
            <i class="fas fa-video"></i> Consultation with
            <?php echo ($_SESSION['role'] == 'patient') ? 'Dr. ' . $appointment['doctor_name'] : $appointment['patient_name']; ?>
        </div>
        <?php if ($_SESSION['role'] == 'doctor'): ?>
            <button onclick="endCall()" class="btn-close">
                <i class="fas fa-sign-out-alt"></i> End Call
            </button>
        <?php else: ?>
            <a href="patient_dashboard.php" class="btn-close">
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
                startWithVideoMuted: false
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                    'security'
                ],
                SHOW_JITSI_WATERMARK: false
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>

</body>

</html>