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
$other_party_name = ($_SESSION['role'] == 'patient') ? $appointment['doctor_name'] : $appointment['patient_name'];
?>

<?php include 'includes/header.php'; ?>

<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: 70vh;
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .chat-header {
        background: var(--primary-color);
        color: white;
        padding: 1rem;
        font-weight: bold;
    }

    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        background: #f9f9f9;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 20px;
        font-size: 0.95rem;
        position: relative;
    }

    .message.sent {
        align-self: flex-end;
        background: var(--primary-color);
        color: white;
        border-bottom-right-radius: 5px;
    }

    .message.received {
        align-self: flex-start;
        background: #e9e9eb;
        color: black;
        border-bottom-left-radius: 5px;
    }

    .chat-input {
        padding: 1rem;
        background: white;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 10px;
    }

    .chat-input input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
    }

    .chat-input button {
        padding: 10px 20px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
    }

    .message-time {
        font-size: 0.7rem;
        opacity: 0.7;
        margin-top: 5px;
        display: block;
        text-align: right;
    }
</style>

<div class="container" style="padding-top: 4rem;">
    <div class="chat-container">
        <div class="chat-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Chat with <?php echo $other_party_name; ?></span>

            <?php if ($_SESSION['role'] == 'doctor'): ?>
                <a href="video_consultation.php?appointment_id=<?php echo $appointment_id; ?>"
                    style="color: white; text-decoration: none;" title="Start Video Call"
                    onclick="return startCallFromChat(event)">
                    <i class="fas fa-video"></i>
                </a>
            <?php elseif (isset($appointment['is_call_active']) && $appointment['is_call_active']): ?>
                <a href="video_consultation.php?appointment_id=<?php echo $appointment_id; ?>"
                    style="color: white; text-decoration: none;" title="Join Video Call">
                    <i class="fas fa-video"></i>
                </a>
            <?php else: ?>
                <span style="opacity: 0.5; cursor: not-allowed;" title="Waiting for doctor to start call"><i
                        class="fas fa-video-slash"></i></span>
            <?php endif; ?>
        </div>
        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be loaded here -->
        </div>
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script>
    const appointmentId = <?php echo $appointment_id; ?>;
    const currentUserId = <?php echo $user_id; ?>;
    const chatBox = document.getElementById('chatMessages');

    function fetchMessages() {
        fetch(`chat_endpoint.php?action=fetch&appointment_id=${appointmentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    chatBox.innerHTML = '';
                    data.messages.forEach(msg => {
                        const div = document.createElement('div');
                        const isSent = msg.sender_id == currentUserId;
                        div.className = `message ${isSent ? 'sent' : 'received'}`;

                        let statusIcon = '';
                        if (isSent) {
                            if (msg.is_read == 1) {
                                statusIcon = '<i class="fas fa-check-double" style="color: #64ffda; margin-left: 5px; font-size: 0.7rem;"></i>'; // Seen
                            } else {
                                statusIcon = '<i class="fas fa-check" style="color: rgba(255,255,255,0.7); margin-left: 5px; font-size: 0.7rem;"></i>'; // Sent
                            }
                        }

                        div.innerHTML = `
                            ${msg.message} 
                            <span class="message-time">
                                ${msg.formatted_time}
                                ${statusIcon}
                            </span>`;
                        chatBox.appendChild(div);
                    });
                    // Auto scroll to bottom
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            });
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message) return;

        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('message', message);

        fetch(`chat_endpoint.php?action=send`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    input.value = '';
                    fetchMessages();
                } else {
                    alert(data.message);
                }
            });
    }

    // Allow Enter key to send
    document.getElementById('messageInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Poll for messages every 3 seconds
    setInterval(fetchMessages, 3000);
    fetchMessages(); // Initial load

    function startCallFromChat(e) {
        // If doctor, we might want to trigger the start call logic implicitly or just let them go to the page
        // But better to update the status.
        e.preventDefault();
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('status', 1);

        fetch('toggle_call_status.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'video_consultation.php?appointment_id=' + appointmentId;
                }
            });
    }
</script>

<?php include 'includes/footer.php'; ?>