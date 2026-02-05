<?php include 'includes/header.php'; ?>

<div class="container section">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; color: var(--secondary-color); margin-bottom: 10px;">Contact Us</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">We'd love to hear from
            you. Reach
            out to us for any queries or support.</p>
    </div>

    <?php
    $msg = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $conn->real_escape_string($_POST['fullname']);
        $email = $conn->real_escape_string($_POST['email']);
        $subject = $conn->real_escape_string($_POST['subject']);
        $message = $conn->real_escape_string($_POST['message']);

        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";

        if ($conn->query($sql) === TRUE) {
            $msg = "<div class='alert-success'>Message sent successfully! We will get back to you soon.</div>";

            // Notify Admins
            $admin_result = $conn->query("SELECT id FROM users WHERE role = 'admin'");
            if ($admin_result->num_rows > 0) {
                $notif_msg = "New support message from $name: $subject";
                while ($admin = $admin_result->fetch_assoc()) {
                    $admin_id = $admin['id'];
                    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($admin_id, '$notif_msg')");
                }
            }

        } else {
            $msg = "<div class='alert-error'>Error: " . $conn->error . "</div>";
        }
    }
    ?>

    <div>
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-wrap: wrap;">
            <!-- Contact Info Side -->
            <div style="flex: 1; min-width: 300px; background: var(--primary-color); padding: 3rem; color: white;">
                <h3 style="color: white; margin-bottom: 2rem; font-size: 1.8rem;">Contact Information</h3>

                <div style="margin-bottom: 2rem; display: flex; gap: 15px;">
                    <i class="fas fa-map-marker-alt" style="font-size: 1.2rem; margin-top: 5px;"></i>
                    <p style="opacity: 0.9;">Green City, VIP Road,<br>Surat, Gujarat, India 395007</p>
                </div>

                <div style="margin-bottom: 2rem; display: flex; gap: 15px;">
                    <i class="fas fa-phone" style="font-size: 1.2rem; margin-top: 5px;"></i>
                    <p style="opacity: 0.9;">+91 98765 43210</p>
                </div>

                <div style="margin-bottom: 2rem; display: flex; gap: 15px;">
                    <i class="fas fa-envelope" style="font-size: 1.2rem; margin-top: 5px;"></i>
                    <p style="opacity: 0.9;">support@telemed.com</p>
                </div>

                <div style="margin-bottom: 3rem; display: flex; gap: 15px;">
                    <i class="fas fa-clock" style="font-size: 1.2rem; margin-top: 5px;"></i>
                    <p style="opacity: 0.9;">Mon - Fri: 8:00 AM - 8:00 PM</p>
                </div>

                <h4 style="color: white; margin-bottom: 1rem;">Follow Us</h4>
                <div style="font-size: 1.5rem; gap: 20px; display: flex;">
                    <a href="#" style="color: white;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: white;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: white;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: white;"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <!-- Contact Form Side -->
            <div style="flex: 1.5; min-width: 350px; padding: 3rem;">
                <h3 style="margin-bottom: 20px; color: var(--secondary-color);">Send Message</h3>
                <?php echo $msg; ?>
                <form action="" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?"
                            style="resize: none;" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Send Message <i
                            class="fas fa-paper-plane" style="margin-left: 8px;"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<div style="width: 100%; height: 400px; background: #eee;">
    <iframe src="https://maps.google.com/maps?q=Surat,Gujarat&hl=en&z=14&output=embed" width="100%" height="100%"
        style="border:0; filter: grayscale(100%);" allowfullscreen="" loading="lazy"></iframe>
</div>

<?php include 'includes/footer.php'; ?>