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
    .chat-layout {
        display: flex;
        flex-direction: column;
        height: 75vh;
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .chat-header {
        background: var(--surface-color);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        background: var(--success-color);
        border-radius: 50%;
        display: inline-block;
    }

    .chat-messages {
        flex: 1;
        padding: 2rem;
        overflow-y: auto;
        background: #F9FAFB;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .message {
        display: flex;
        flex-direction: column;
        max-width: 70%;
        position: relative;
    }

    .message.sent {
        align-self: flex-end;
        align-items: flex-end;
    }

    .message.received {
        align-self: flex-start;
        align-items: flex-start;
    }

    .message-bubble {
        padding: 12px 20px;
        border-radius: 18px;
        font-size: 1rem;
        line-height: 1.5;
        position: relative;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .message.sent .message-bubble {
        background: var(--primary-color);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.received .message-bubble {
        background: white;
        color: var(--text-main);
        border: 1px solid #e0e0e0;
        border-bottom-left-radius: 4px;
    }

    .message-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .chat-input-area {
        padding: 1.5rem;
        background: white;
        border-top: 1px solid #eee;
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .chat-input-area input {
        flex: 1;
        padding: 15px 20px;
        border: 1px solid #e0e0e0;
        border-radius: 30px;
        outline: none;
        font-family: var(--font-body);
        transition: var(--transition-smooth);
        background: #f8f9fa;
    }

    .chat-input-area input:focus {
        background: white;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
    }

    .btn-send {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
        box-shadow: 0 4px 10px rgba(108, 92, 231, 0.3);
    }

    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(108, 92, 231, 0.4);
    }

    /* Scrollbar Styling */
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
</style>

<div class="container section">
    <div class="chat-layout">
        <div class="chat-header">
            <h3>
                <div
                    style="background: rgba(0, 184, 148, 0.1); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success-color);">
                    <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <?php echo $other_party_name; ?>
                    <span
                        style="display: block; font-size: 0.8rem; color: var(--success-color); font-weight: normal; margin-top: 2px;">
                        <span class="status-dot"></span> Online for Consultation
                    </span>
                </div>
            </h3>

            <?php if ($_SESSION['role'] == 'doctor'): ?>
                <a href="video_consultation.php?appointment_id=<?php echo $appointment_id; ?>" class="btn btn-primary"
                    onclick="return startCallFromChat(event)">
                    <i class="fas fa-video"></i> Start Video Call
                </a>
            <?php elseif (isset($appointment['is_call_active']) && $appointment['is_call_active']): ?>
                <a href="video_consultation.php?appointment_id=<?php echo $appointment_id; ?>" class="btn btn-primary"
                    style="background: #6c5ce7; animation: pulse 2s infinite;">
                    <i class="fas fa-video"></i> Join Video Call
                </a>
            <?php else: ?>
                <button class="btn btn-outline" disabled title="Waiting for doctor to start call">
                    <i class="fas fa-video-slash"></i> Video Call
                </button>
            <?php endif; ?>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- Messages loaded via JS -->
            <div style="text-align: center; color: var(--text-muted); margin-top: 2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading conversation...
            </div>
        </div>

        <div class="chat-input-area">
            <button class="btn btn-outline"
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: #f0f0f0; color: #777;">
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off">
            <button class="btn-send" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
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
                    if (data.messages.length === 0) {
                        chatBox.innerHTML = '<div style="text-align: center; color: var(--text-muted); margin-top: 2rem;">No messages yet. Start the conversation!</div>';
                    }
                    data.messages.forEach(msg => {
                        const div = document.createElement('div');
                        const isSent = msg.sender_id == currentUserId;
                        div.className = `message ${isSent ? 'sent' : 'received'}`;

                        let statusIcon = '';
                        if (isSent) {
                            if (msg.is_read == 1) {
                                statusIcon = '<i class="fas fa-check-double" style="color: var(--primary-color);"></i>';
                            } else {
                                statusIcon = '<i class="fas fa-check"></i>';
                            }
                        }

                        div.innerHTML = `
                            <div class="message-bubble">
                                ${msg.message}
                            </div>
                            <div class="message-time">
                                ${msg.formatted_time}
                                ${statusIcon}
                            </div>`;
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