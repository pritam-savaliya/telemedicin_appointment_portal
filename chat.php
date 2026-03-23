<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

if (!isset($_GET['appointment_id'])) {
    header("Location: " . BASE_URL . "messages.php");
    exit();
}

$appointment_id = $_GET['appointment_id'];
$user_id = $_SESSION['user_id'];

// Verify access and get appointment details
$check_sql = "SELECT appointments.*, 
              d.fullname as doctor_name, d.profile_pic as doctor_pic,
              p.fullname as patient_name, p.profile_pic as patient_pic,
              appointments.id as appointment_id
              FROM appointments 
              JOIN users d ON appointments.doctor_id = d.id
              JOIN users p ON appointments.patient_id = p.id
              WHERE appointments.id = $appointment_id AND (patient_id = $user_id OR doctor_id = $user_id) AND (status = 'confirmed' OR status = 'completed')";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows == 0) {
    include 'includes/header.php'; 
    echo '<div class="container section" style="text-align: center; padding: 100px 0;">
            <div class="glass-card" style="max-width: 600px; margin: 0 auto; padding: 4rem;">
                <i class="fas fa-comments-slash" style="font-size: 4rem; color: var(--accent); margin-bottom: 2rem; opacity: 0.5;"></i>
                <h2 style="margin-bottom: 1rem;">Messenger Unavailable</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Access Denied: This appointment might not be confirmed yet, or you are not authorized to access this private channel.</p>
                <a href="messages.php" class="btn btn-primary">Back to Inbox</a>
            </div>
          </div>';
    include 'includes/footer.php';
    exit();
}

$appointment = $check_result->fetch_assoc();
$is_doctor = ($_SESSION['role'] == 'doctor');
$other_party_name = $is_doctor ? $appointment['patient_name'] : $appointment['doctor_name'];
$other_party_pic = $is_doctor ? $appointment['patient_pic'] : $appointment['doctor_pic'];
$other_party_pic_url = !empty($other_party_pic) ? BASE_URL . 'assets/uploads/profile_pics/' . $other_party_pic : 'https://ui-avatars.com/api/?name=' . urlencode($other_party_name) . '&background=random&color=fff&size=200';
?>

<?php include 'includes/header.php'; ?>

<div class="dashboard-layout fade-in">
    <!-- Role-Based Navigation Sidebar -->
    <?php if ($_SESSION['role'] == 'patient'): ?>
        <aside class="sidebar">
            <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
                <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                    <?php 
                    $nav_pic = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $_SESSION['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=200';
                    ?>
                    <img src="<?php echo $nav_pic; ?>" 
                         style="width: 70px; height: 70px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname']); ?>&background=random&color=fff&size=200'">
                    <div style="position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;"><?php echo $_SESSION['fullname']; ?></h4>
                <span class="badge badge-info" style="font-size: 0.6rem; opacity: 0.8; background: rgba(99,102,241,0.1); color: var(--primary);">Patient Portal</span>
            </div>

            <a href="patient/patient_dashboard.php" class="sidebar-item"><i class="fas fa-th-large"></i> Overview</a>
            <a href="patient/book_appointment.php" class="sidebar-item"><i class="fas fa-calendar-plus"></i> New Consultation</a>
            <a href="patient/my_appointments.php" class="sidebar-item"><i class="fas fa-history"></i> Consultation History</a>
            <a href="patient/upload_report.php" class="sidebar-item"><i class="fas fa-file-medical"></i> Medical Records</a>
            <a href="patient/my_prescriptions.php" class="sidebar-item"><i class="fas fa-pills"></i> My Prescriptions</a>
            <a href="messages.php" class="sidebar-item active"><i class="fas fa-comments"></i> Messages</a>

            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="profile.php" class="sidebar-item"><i class="fas fa-user-gear"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>
    <?php elseif ($_SESSION['role'] == 'doctor'): ?>
        <aside class="sidebar">
            <div style="padding: 1rem; margin-bottom: 2rem; text-align: center;">
                <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                    <?php 
                    $doc_nav_pic = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) && $_SESSION['profile_pic'] != 'default_user.png') ? BASE_URL . 'assets/uploads/profile_pics/' . $_SESSION['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['fullname']) . '&background=random&color=fff&size=200';
                    ?>
                    <img src="<?php echo $doc_nav_pic; ?>" 
                         style="width: 70px; height: 70px; border-radius: 20px; border: 2px solid var(--primary); object-fit: cover; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['fullname']); ?>&background=random&color=fff&size=200'">
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 16px; height: 16px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-sidebar);"></div>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Dr. <?php echo str_replace('Dr. ', '', $_SESSION['fullname']); ?></h4>
                <span class="badge badge-success" style="font-size: 0.6rem; opacity: 0.8; background: rgba(16,185,129,0.1); color: var(--success);">Verified Clinician</span>
            </div>

            <a href="doctor/doctor_dashboard.php" class="sidebar-item"><i class="fas fa-chart-line"></i> Command Center</a>
            <a href="doctor/manage_schedule.php" class="sidebar-item"><i class="fas fa-calendar-check"></i> My Schedule</a>
            <a href="doctor/view_patient_records.php" class="sidebar-item"><i class="fas fa-user-injured"></i> Patient Ledger</a>
            <a href="doctor/reports.php" class="sidebar-item"><i class="fas fa-file-contract"></i> Clinical Analytics</a>
            <a href="messages.php" class="sidebar-item active"><i class="fas fa-comments"></i> Consultations</a>

            <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border-glass);">
                <a href="profile.php" class="sidebar-item"><i class="fas fa-user-doctor"></i> My Profile</a>
                <a href="auth/logout.php" class="sidebar-item" style="color: var(--accent);"><i class="fas fa-power-off"></i> Sign Out</a>
            </div>
        </aside>
    <?php endif; ?>

    <main class="main-content">
        <div class="chat-container" style="height: calc(100vh - 180px); padding: 0;">
            <!-- Sidebar with info -->
            <aside class="chat-room-glass chat-sidebar-info">
                <div class="glass-card" style="padding: 1.5rem; text-align: center; background: var(--bg-glass);">
                    <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                        <img src="<?php echo $other_party_pic_url; ?>" 
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary);" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($other_party_name); ?>&background=random&color=fff&size=200'">
                        <div class="status-dot-pulse" style="position: absolute; bottom: 8px; right: 8px; width: 15px; height: 15px;"></div>
                    </div>
                    <h4 style="margin-bottom: 0.2rem;"><?php echo $other_party_name; ?></h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo $is_doctor ? 'Patient' : 'Specialist Doctor'; ?></p>
                </div>

                <div class="glass-card" style="padding: 1.2rem; background: var(--bg-glass);">
                    <h5 style="margin-bottom: 1rem; font-size: 0.9rem; color: var(--primary);">Appointment Info</h5>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Date:</span>
                            <span style="font-weight: 600;"><?php echo date('M d, Y', strtotime($appointment['date'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Time:</span>
                            <span style="font-weight: 600;"><?php echo date('h:i A', strtotime($appointment['time'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Consultation ID:</span>
                            <span style="font-weight: 600;">#<?php echo $appointment_id; ?></span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: auto;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; opacity: 0.6;">
                        <i class="fas fa-shield-alt"></i> End-to-end encrypted
                    </p>
                </div>
            </aside>

            <!-- Main Chat Area -->
            <main class="chat-room-glass">
                <header class="chat-header-banner">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="status-indicator">
                            <div class="status-dot-pulse"></div>
                            <span style="font-weight: 600;"><?php echo $other_party_name; ?></span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <!-- Language Selector -->
                        <div class="lang-selector-wrapper" style="display: flex; align-items: center; gap: 8px; background: var(--bg-dark); padding: 5px 12px; border-radius: 50px; border: 1px solid var(--border-glass);">
                            <i class="fas fa-language" style="color: var(--primary);"></i>
                            <select id="chatLanguage" style="background: transparent; border: none; color: var(--text-main); font-size: 0.85rem; cursor: pointer; outline: none;">
                                <option value="en" selected>English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                                <option value="de">German</option>
                                <option value="hi">Hindi</option>
                                <option value="zh">Chinese</option>
                                <option value="ja">Japanese</option>
                            </select>
                        </div>

                        <?php if ($is_doctor): ?>
                            <a href="video_consultation.php?appointment_id=<?php echo $appointment_id; ?>" 
                               class="btn btn-primary" style="padding: 0.6rem 1.2rem; border-radius: 12px;"
                               onclick="return startCallFromChat(event)">
                                <i class="fas fa-video"></i> <span class="hide-mobile">Start Call</span>
                            </a>
                        <?php elseif (isset($appointment['is_call_active']) && $appointment['is_call_active']): ?>
                            <a href="video_consultation.php?appointment_id=<?php echo $appointment_id; ?>" 
                               class="btn btn-primary" style="padding: 0.6rem 1.2rem; border-radius: 12px; background: var(--success); animation: pulse 2s infinite;">
                                <i class="fas fa-video"></i> <span class="hide-mobile">Join Call</span>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled title="Waiting for doctor to start call" 
                                    style="padding: 0.6rem 1.2rem; border-radius: 12px; opacity: 0.5;">
                                <i class="fas fa-video-slash"></i> <span class="hide-mobile">Video Call</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="chat-messages-area" id="chatMessages">
                    <div style="text-align: center; color: var(--text-muted); padding: 5rem;">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p style="margin-top: 1rem;">Opening secure channel...</p>
                    </div>
                </div>

                <div class="chat-input-wrapper">
                    <button class="btn-ghost" title="Attach file" onclick="window.location.href='<?php echo $is_doctor ? 'doctor/write_prescription.php?appointment_id=' . $appointment_id : 'patient/upload_report.php'; ?>'" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-plus"></i>
                    </button>
                    <input type="text" id="messageInput" class="chat-input-field" placeholder="Type your message here..." autocomplete="off">
                    <button class="btn-send-chat" id="sendBtn" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </main>
        </div>
    </main>
</div>

<script>
    const appointmentId = <?php echo $appointment_id; ?>;
    const currentUserId = <?php echo $user_id; ?>;
    const chatBox = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const langSelector = document.getElementById('chatLanguage');

    const translationCache = new Map();
    let currentLanguage = localStorage.getItem('chat_language_v2') || 'en';
    langSelector.value = currentLanguage;

    langSelector.addEventListener('change', function() {
        currentLanguage = this.value;
        localStorage.setItem('chat_language_v2', currentLanguage);
        fetchMessages(true);
    });

    async function translateText(text, targetLang) {
        if (targetLang === 'en') return text;
        const cacheKey = targetLang + '_' + btoa(unescape(encodeURIComponent(text)));
        if (translationCache.has(cacheKey)) return translationCache.get(cacheKey);

        try {
            const response = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=en|${targetLang}`);
            const data = await response.json();
            if (data.responseData && data.responseData.translatedText) {
                const translated = data.responseData.translatedText;
                translationCache.set(cacheKey, translated);
                return translated;
            }
        } catch (e) {
            console.error('Translation error:', e);
        }
        return text;
    }

    let isFetching = false;
    let lastMessageCount = 0;
    let autoScroll = true;

    chatBox.addEventListener('scroll', () => {
        const threshold = 100;
        autoScroll = (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < threshold;
    });

    async function fetchMessages(forceRender = false) {
        if (isFetching && !forceRender) return;
        isFetching = true;

        try {
            const response = await fetch(`api/chat_endpoint.php?action=fetch&appointment_id=${appointmentId}`);
            const data = await response.json();

            if (data.status === 'success') {
                if (data.messages.length === 0) {
                    chatBox.innerHTML = `
                        <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0.5;">
                            <i class="far fa-comments fa-3x" style="margin-bottom: 1rem;"></i>
                            <p>No messages yet. Say hello!</p>
                        </div>`;
                    isFetching = false;
                    return;
                }

                if (data.messages.length !== lastMessageCount || forceRender) {
                    chatBox.innerHTML = '';
                    for (const msg of data.messages) {
                        const isSent = msg.sender_id == currentUserId;
                        const div = document.createElement('div');
                        div.className = `msg-bubble ${isSent ? 'msg-sent' : 'msg-received'}`;

                        let displayMessage = msg.message;
                        if (!isSent && currentLanguage !== 'en') {
                            displayMessage = await translateText(msg.message, currentLanguage);
                            displayMessage = `<div>${displayMessage}</div><div style="font-size: 0.75em; opacity: 0.6; margin-top: 4px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 4px;">${msg.message}</div>`;
                        }

                        let statusIcon = '';
                        if (isSent) {
                            statusIcon = msg.is_read == 1 ? '<i class="fas fa-check-double" style="color: #4ade80;"></i>' : '<i class="fas fa-check"></i>';
                        }

                        div.innerHTML = `
                            <div class="msg-text">${displayMessage}</div>
                            <div class="msg-info">
                                ${msg.formatted_time} ${statusIcon}
                            </div>
                        `;
                        chatBox.appendChild(div);
                    }
                    
                    lastMessageCount = data.messages.length;
                    if (autoScroll || forceRender) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                }
            }
        } catch (err) {
            console.error('Fetch error:', err);
        }
        isFetching = false;
    }

    function sendMessage() {
        const text = messageInput.value.trim();
        if (!text) return;

        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('message', text);

        messageInput.value = '';
        messageInput.focus();

        fetch(`api/chat_endpoint.php?action=send`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                fetchMessages(true);
            } else {
                alert('Failed to send: ' + data.message);
            }
        });
    }

    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    function startCallFromChat(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('status', 1);

        fetch('api/toggle_call_status.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'video_consultation.php?appointment_id=' + appointmentId;
                }
            });
    }

    setInterval(fetchMessages, 3000);
    fetchMessages(true);
</script>

<style>
    @media (max-width: 768px) {
        .hide-mobile { display: none; }
        .chat-container { height: calc(100vh - 120px); margin: 0; }
        .msg-bubble { max-width: 85%; }
    }
</style>

<?php include 'includes/footer.php'; ?>