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
                style="width: 40px; height: 40px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; border: none; background: transparent; color: var(--text-muted);">
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

    // Translation Logic
    const translationCache = new Map(); // Store translations: "msgID_langCode" => "Text"
    let currentLanguage = localStorage.getItem('chat_language') || 'en';

    // Create and Insert Language Selector
    const headerDiv = document.querySelector('.chat-header');
    const langSelect = document.createElement('select');
    langSelect.id = 'languageSelector';
    langSelect.className = 'form-control';
    langSelect.style.width = 'auto';
    langSelect.style.marginLeft = 'auto';
    langSelect.style.marginRight = '10px';
    langSelect.style.padding = '5px 10px';

    const languages = [
        { code: 'en', name: 'English' },
        { code: 'es', name: 'Spanish' },
        { code: 'fr', name: 'French' },
        { code: 'de', name: 'German' },
        { code: 'hi', name: 'Hindi' },
        { code: 'zh', name: 'Chinese' },
        { code: 'ar', name: 'Arabic' },
        { code: 'ru', name: 'Russian' },
        { code: 'ja', name: 'Japanese' }
    ];

    languages.forEach(lang => {
        const option = document.createElement('option');
        option.value = lang.code;
        option.text = lang.name;
        if (lang.code === currentLanguage) option.selected = true;
        langSelect.appendChild(option);
    });

    // Insert before the buttons
    const headerButtons = headerDiv.querySelectorAll('a, button');
    if (headerButtons.length > 0) {
        headerDiv.insertBefore(langSelect, headerButtons[0]);
    } else {
        headerDiv.appendChild(langSelect);
    }

    langSelect.addEventListener('change', function () {
        currentLanguage = this.value;
        localStorage.setItem('chat_language', currentLanguage);
        // Clear cache for new language to force re-translate or use new cache key
        // actually we don't need to clear, just re-render
        fetchMessages(true); // Force re-render
    });

    async function translateText(text, targetLang) {
        if (targetLang === 'en') return text; // Assuming base is English (or we can detect)

        // Simple cache key
        const cacheKey = btoa(unescape(encodeURIComponent(text))) + '_' + targetLang;
        if (translationCache.has(cacheKey)) {
            return translationCache.get(cacheKey);
        }

        try {
            // MyMemory API
            const response = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=Autodetect|${targetLang}`);
            const data = await response.json();

            if (data.responseData && data.responseData.translatedText) {
                const translated = data.responseData.translatedText;
                translationCache.set(cacheKey, translated);
                return translated;
            }
        } catch (e) {
            console.error('Translation error:', e);
        }
        return text; // Fallback to original
    }

    let isFetching = false;

    function fetchMessages(forceRender = false) {
        if (isFetching && !forceRender) return;
        isFetching = true;

        fetch(`api/chat_endpoint.php?action=fetch&appointment_id=${appointmentId}`)
            .then(response => response.json())
            .then(async data => {
                if (data.status === 'success') {
                    // Check if we need to re-render (naive check: count changed or force)
                    // Better: check last message ID or rely on always re-rendering but efficiently
                    // For this simple app, we clear and rebuild or just append. 
                    // Current implementation flushes content. Let's optimize slightly or just follow existing pattern.

                    // To support translation cleanly, we'll re-render all for now to ensure all get translated
                    // In a production app, we would only append new ones or update existing.

                    chatBox.innerHTML = '';
                    if (data.messages.length === 0) {
                        chatBox.innerHTML = '<div style="text-align: center; color: var(--text-muted); margin-top: 2rem;">No messages yet. Start the conversation!</div>';
                    }

                    for (const msg of data.messages) {
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

                        // Translate if it's a received message, OR translate everything if user wants UI in their language
                        // Logic: Translate received messages to currentLanguage.
                        // Optional: Translate Sent messages too? Usually native speakers want to see what they wrote.
                        // Let's translate ONLY received messages for clarity, or everything.
                        // Requirement: "get message in that perticular language"

                        let displayMessage = msg.message;
                        if (!isSent && currentLanguage !== 'en') {
                            displayMessage = await translateText(msg.message, currentLanguage);
                            // Add a small indicator
                            displayMessage += ` <small style="font-size:0.6em; opacity:0.6; display:block;">(Ref: ${msg.message})</small>`;
                        }

                        div.innerHTML = `
                            <div class="message-bubble">
                                ${displayMessage}
                            </div>
                            <div class="message-time">
                                ${msg.formatted_time}
                                ${statusIcon}
                            </div>`;
                        chatBox.appendChild(div);
                    }

                    // Auto scroll to bottom only if at bottom or first load
                    // For now, always scroll like original
                    // chatBox.scrollTop = chatBox.scrollHeight;
                }
                isFetching = false;
            })
            .catch(() => isFetching = false);
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message) return;

        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('message', message);

        fetch(`api/chat_endpoint.php?action=send`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    input.value = '';
                    fetchMessages(true);
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

    // Poll for messages every 5 seconds to be nicer to the translation API rate limits
    setInterval(() => fetchMessages(), 5000);
    fetchMessages(); // Initial load

    function startCallFromChat(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('status', 1);

        fetch('api/toggle_call_status.php', {
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