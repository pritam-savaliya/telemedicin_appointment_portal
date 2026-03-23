<?php include 'includes/header.php'; ?>

<div class="container fade-in" style="padding-top: 5rem; padding-bottom: 5rem;">
    <div style="text-align: center; margin-bottom: 5rem;">
        <div class="badge badge-success" style="margin-bottom: 1.5rem;"><i class="fas fa-headset"></i> 24/7 GLOBAL SUPPORT</div>
        <h1 style="font-size: 3.5rem; margin-bottom: 1rem;">Let's Start a <span style="color: var(--primary);">Conversation</span>.</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.7;">
            Have questions about our clinical network or need technical assistance? Our team is standing by to help you.
        </p>
    </div>

    <?php
    $status_msg = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $conn->real_escape_string($_POST['fullname']);
        $email = $conn->real_escape_string($_POST['email']);
        $subject = $conn->real_escape_string($_POST['subject']);
        $message = $conn->real_escape_string($_POST['message']);

        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
        if ($conn->query($sql) === TRUE) {
            $status_msg = "<div class='badge badge-success' style='width: 100%; padding: 1.2rem; margin-bottom: 2rem;'><i class='fas fa-check-circle'></i> Transmission successful. We will respond shortly.</div>";
            // Admin notification logic remains standard
        }
    }
    ?>

    <div class="glass-card" style="padding: 0; overflow: hidden; display: grid; grid-template-columns: 1fr 1.5fr;">
        <!-- Info Panel -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); padding: 4rem; color: white; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -10%; right: -10%; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; blur: 50px;"></div>
            
            <h2 style="color: white; font-size: 2.2rem; margin-bottom: 3rem;">Clinical Command</h2>
            
            <div style="display: grid; gap: 2.5rem;">
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <i class="fas fa-location-dot" style="font-size: 1.2rem; margin-top: 5px; color: rgba(255,255,255,0.7);"></i>
                    <div>
                        <h4 style="color: white; margin-bottom: 0.3rem;">Digital Headquarters</h4>
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem;">Green City Tech Hub, VIP Road, Surat, GJ 395007</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <i class="fas fa-phone-volume" style="font-size: 1.2rem; margin-top: 5px; color: rgba(255,255,255,0.7);"></i>
                    <div>
                        <h4 style="color: white; margin-bottom: 0.3rem;">Priority Line</h4>
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem;">+91 98765 43210</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <i class="fas fa-envelope-open-text" style="font-size: 1.2rem; margin-top: 5px; color: rgba(255,255,255,0.7);"></i>
                    <div>
                        <h4 style="color: white; margin-bottom: 0.3rem;">Support Email</h4>
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem;">ops@medconnect.sys</p>
                    </div>
                </div>
            </div>

            <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <h4 style="color: white; margin-bottom: 1.5rem; font-size: 0.8rem; letter-spacing: 2px; text-transform: uppercase;">Connect securely</h4>
                <div style="display: flex; gap: 1rem;">
                    <a href="#" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.1); width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px;"><i class="fab fa-linkedin"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.1); width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px;"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.1); width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px;"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div style="padding: 4rem;">
            <?php echo $status_msg; ?>
            <form action="" method="POST" style="display: grid; gap: 2rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="fullname" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Inquiry Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Platform Access / Clinical Inquiry" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Detailed Message</label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Describe your requirement in detail..." required style="resize: none;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; margin-top: 1rem;">
                    Send Secure Transmission <i class="fas fa-paper-plane" style="margin-left: 10px;"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<div style="width: 100%; height: 450px; background: var(--bg-card); position: relative;">
    <iframe src="https://maps.google.com/maps?q=Surat,Gujarat&hl=en&z=14&output=embed" width="100%" height="100%" style="border:0; filter: invert(90%) hue-rotate(180deg) opacity(0.5);" allowfullscreen="" loading="lazy"></iframe>
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; background: linear-gradient(to bottom, var(--bg-dark), transparent 10%, transparent 90%, var(--bg-dark));"></div>
</div>

<?php include 'includes/footer.php'; ?>