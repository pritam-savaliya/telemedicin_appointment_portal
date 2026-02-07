<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<?php include '../includes/header.php'; ?>

<?php
$patient_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['c'];
$patient_active = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $user_id AND (status = 'confirmed' OR status = 'pending')")->fetch_assoc()['c'];
?>

<div class="container section">

    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; color: var(--secondary-color); margin-bottom: 10px;">Welcome back,
            <?php echo explode(' ', $_SESSION['fullname'])[0]; ?>!
        </h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Manage your health journey with ease.</p>
    </div>

    <!-- Stats -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); padding: 15px; border-radius: 50%; color: var(--success-color);">
                <i class="fas fa-heartbeat" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $patient_active; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Active Consultations</span>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); padding: 15px; border-radius: 50%; color: var(--secondary-color);">
                <i class="fas fa-check-circle" style="font-size: 1.8rem;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 2rem;"><?php echo $patient_completed; ?></h3>
                <span style="color: var(--text-muted); font-size: 0.9rem;">Completed Treatments</span>
            </div>
        </div>
    </div>

    <!-- Emergency Action -->
    <div onclick="window.location.href='emergency_booking.php'" class="card"
        style="background: linear-gradient(135deg, #ff7675, #d63031); color: white; cursor: pointer; margin-bottom: 30px; transition: transform 0.2s; display: flex; align-items: center; justify-content: space-between; padding: 30px;">
        <div>
            <h2 style="margin: 0; color: white; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-ambulance" style="font-size: 2rem;"></i> Emergency Assistance
            </h2>
            <p style="margin: 10px 0 0; color: rgba(255,255,255,0.9);">
                Immediate booking for critical situations. Find nearby hospitals instantly.
            </p>
        </div>
        <i class="fas fa-chevron-right" style="font-size: 1.5rem;"></i>
    </div>

    <!-- Quick Actions -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 4rem;">
        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(108, 92, 231, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                <i class="fas fa-user-md" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Find a Doctor</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Browse our list of specialists and book your next
                consultation online.</p>
            <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(0, 184, 148, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">My Appointments</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Track your upcoming visits and view past
                consultation history.</p>
            <a href="my_appointments.php" class="btn btn-primary">View History</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">
                <i class="fas fa-file-medical" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Medical Records</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Upload and manage your medical history and
                reports.</p>
            <a href="upload_report.php" class="btn btn-primary">Upload Reports</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(108, 117, 125, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">
                <i class="fas fa-user-circle" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Profile Settings</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Update your personal details and manage your
                account preference.</p>
            <a href="../profile.php" class="btn btn-primary">Edit Profile</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px 30px;">
            <div
                style="background: rgba(46, 204, 113, 0.1); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
                <i class="fas fa-share-alt" style="font-size: 2.5rem;"></i>
            </div>
            <h3 style="margin-bottom: 15px;">Share App</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Share TeleMed with friends and family via Link or
                QR.</p>
            <button onclick="openShareModal()" class="btn btn-primary">Share Now</button>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <span class="close-modal" onclick="closeShareModal()">&times;</span>
            <div style="text-align: center;">
                <h2 style="color: var(--secondary-color); margin-bottom: 15px;">Share TeleMed</h2>
                <p style="color: var(--text-muted); margin-bottom: 25px;">Scan the QR code or copy the link below.</p>

                <div
                    style="background: white; padding: 15px; display: inline-block; border-radius: 10px; border: 1px solid #eee; margin-bottom: 20px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode(BASE_URL); ?>"
                        alt="App QR Code" title="Scan to visit">
                </div>

                <div class="input-group" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" value="<?php echo BASE_URL; ?>" id="shareLink" readonly class="form-control"
                        style="text-align: center;">
                    <button onclick="copyToClipboard()" class="btn btn-secondary" title="Copy Link">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button onclick="toggleEmailShare()" class="btn btn-primary" title="Send via Email">
                        <i class="fas fa-envelope"></i>
                    </button>
                </div>

                <!-- Email Share Section -->
                <div id="emailShareSection" style="display: none; margin-bottom: 20px; animation: fadeIn 0.3s;">
                    <div style="display: flex; gap: 10px;">
                        <input type="email" id="shareEmail" class="form-control" placeholder="Enter recipient's email"
                            required>
                        <button onclick="sendShareEmail()" class="btn btn-primary">Send</button>
                    </div>
                </div>

                <p id="copyMsg"
                    style="color: var(--success-color); font-size: 0.9rem; opacity: 0; transition: opacity 0.3s;">Link
                    copied to clipboard!</p>
                <p id="emailMsg" style="font-size: 0.9rem; display: none; margin-top: 10px;"></p>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 450px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
        }

        .close-modal:hover {
            color: #333;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        function openShareModal() {
            document.getElementById('shareModal').style.display = 'flex';
        }

        function closeShareModal() {
            document.getElementById('shareModal').style.display = 'none';
        }

        function toggleEmailShare() {
            var section = document.getElementById('emailShareSection');
            if (section.style.display === 'none') {
                section.style.display = 'block';
                document.getElementById('shareEmail').focus();
            } else {
                section.style.display = 'none';
            }
        }

        function sendShareEmail() {
            var email = document.getElementById('shareEmail').value;
            var msg = document.getElementById('emailMsg');

            if (!email) {
                alert('Please enter an email address');
                return;
            }

            msg.style.display = 'block';
            msg.style.color = 'var(--text-muted)';
            msg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            var formData = new FormData();
            formData.append('email', email);

            fetch('<?php echo BASE_URL; ?>api/send_share_email.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        msg.style.color = 'var(--success-color)';
                        msg.innerText = 'Email sent successfully!';
                        document.getElementById('shareEmail').value = '';
                        setTimeout(() => { toggleEmailShare(); msg.style.display = 'none'; }, 2000);
                    } else {
                        msg.style.color = 'var(--danger-color)';
                        msg.innerText = data.error || 'Failed to send email.';
                    }
                })
                .catch(err => {
                    console.error(err);
                    msg.style.color = 'var(--danger-color)';
                    msg.innerText = 'Error sending email.';
                });
        }

        function copyToClipboard() {
            var copyText = document.getElementById("shareLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */

            navigator.clipboard.writeText(copyText.value).then(function () {
                var msg = document.getElementById("copyMsg");
                msg.style.opacity = "1";
                setTimeout(function () {
                    msg.style.opacity = "0";
                }, 2000);
            }, function (err) {
                console.error('Async: Could not copy text: ', err);
            });
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            var modal = document.getElementById('shareModal');
            if (event.target == modal) {
                closeShareModal();
            }
        }
    </script>

    <?php
    $notif_sql = "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = FALSE ORDER BY created_at DESC";
    $notif_result = $conn->query($notif_sql);

    $chat_sql = "SELECT appointments.*, users.fullname as doctor_name,
                 (SELECT COUNT(*) FROM chat_messages WHERE appointment_id = appointments.id AND is_read = 0 AND sender_id != $user_id) as unread_msgs
                 FROM appointments 
                 JOIN users ON appointments.doctor_id = users.id 
                 WHERE patient_id = $user_id AND appointments.status = 'confirmed' 
                 ORDER BY appointments.date DESC LIMIT 5";
    $chat_result = $conn->query($chat_sql);
    ?>



    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px;">

        <!-- Notifications -->
        <div>
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-bell" style="color: var(--warning-color);"></i> Notifications
            </h3>
            <?php if ($notif_result->num_rows > 0): ?>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <?php while ($notif = $notif_result->fetch_assoc()): ?>
                        <div id="notif-<?php echo $notif['id']; ?>"
                            style="border-left: 4px solid var(--primary-color); padding: 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: start;">
                            <span style="font-size: 0.95rem; color: var(--text-main);"><?php echo $notif['message']; ?></span>
                            <button onclick="dismissNotification(<?php echo $notif['id']; ?>)"
                                style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">&times;</button>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 30px; color: var(--text-muted);">
                    All caught up! No new notifications.
                </div>
            <?php endif; ?>
        </div>

        <!-- Active Consultations -->
        <div>
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-comments" style="color: var(--success-color);"></i> Active Consultations
            </h3>
            <?php if ($chat_result->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while ($appt = $chat_result->fetch_assoc()): ?>
                        <div class="card"
                            style="padding: 20px; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid var(--success-color);">
                            <div>
                                <h4 style="margin-bottom: 5px;">Dr. <?php echo $appt['doctor_name']; ?></h4>
                                <div style="display: flex; gap: 15px; font-size: 0.9rem; color: var(--text-muted);">
                                    <span><i class="far fa-calendar"></i>
                                        <?php echo date('M d', strtotime($appt['date'])); ?></span>
                                    <span><i class="far fa-clock"></i>
                                        <?php echo date("h:i A", strtotime($appt['time'])); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="../chat.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-secondary"
                                    style="padding: 8px 15px; font-size: 0.9rem; position: relative;">
                                    <i class="fas fa-paper-plane"></i>
                                    <?php if ($appt['unread_msgs'] > 0): ?>
                                        <span
                                            style="position: absolute; top: -5px; right: -5px; background: var(--danger-color); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;">
                                            <?php echo $appt['unread_msgs']; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <?php if (isset($appt['is_call_active']) && $appt['is_call_active']): ?>
                                    <a href="../video_consultation.php?appointment_id=<?php echo $appt['id']; ?>"
                                        class="btn btn-primary"
                                        style="padding: 8px 15px; font-size: 0.9rem; background-color: #6c5ce7;">
                                        <i class="fas fa-video"></i>
                                    </a>
                                <?php else: ?>
                                    <button disabled class="btn"
                                        style="padding: 8px 15px; background-color: #e0e0e0; color: #999; cursor: not-allowed; font-size: 0.9rem;">
                                        <i class="fas fa-video-slash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 30px; color: var(--text-muted);">
                    No active consultations when logged in.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function dismissNotification(id) {
        const formData = new FormData();
        formData.append('id', id);
        fetch('<?php echo BASE_URL; ?>api/get_notifications.php?action=mark_read', {
            method: 'POST',
            body: formData
        }).then(() => {
            document.getElementById('notif-' + id).style.display = 'none';
        });
    }
</script>

<?php include '../includes/footer.php'; ?>