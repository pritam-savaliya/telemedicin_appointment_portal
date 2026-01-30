<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<div
    style="background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); color: var(--bg-white); padding: 5rem 5% 3rem; text-align: center;">
    <h1 style="font-size: 3rem; margin-bottom: 1rem; color: var(--bg-white);">Contact Us</h1>
    <p style="font-size: 1.2rem; opacity: 0.9;">We'd love to hear from you. Reach out to us for any queries or support.
    </p>
</div>

<?php
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // In a real app, we would save to DB or send email
    // For now, just show success
    $msg = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;'>Message sent successfully! We will get back to you soon.</div>";
}
?>

<section class="section" style="margin-top: -3rem;">
    <div class="contact-container">
        <!-- Contact Info Side -->
        <div class="contact-info">

            <div class="contact-details">
                <div>
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Medical Plaza, Health Street, New York, NY 10001</span>
                </div>
                <div>
                    <i class="fas fa-phone"></i>
                    <span>+1 (555) 123-4567</span>
                </div>
                <div>
                    <i class="fas fa-envelope"></i>
                    <span>support@telemed.com</span>
                </div>
                <div>
                    <i class="fas fa-clock"></i>
                    <span>Mon - Fri: 8:00 AM - 8:00 PM</span>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <h4 style="margin-bottom: 1rem;">Follow Us</h4>
                <div style="font-size: 1.5rem; gap: 15px; display: flex;">
                    <i class="fab fa-facebook"></i>
                    <i class="fab fa-twitter"></i>
                    <i class="fab fa-instagram"></i>
                    <i class="fab fa-linkedin"></i>
                </div>
            </div>
        </div>

        <!-- Contact Form Side -->
        <div class="contact-form-wrapper">
            <h3>Send Message</h3>
            <?php echo $msg; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" class="form-control" placeholder="Subject" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" rows="5" placeholder="How can we help you?" style="resize: none;"
                        required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- Optional Map Section -->
<div
    style="width: 100%; height: 400px; background: #eee; display: flex; align-items: center; justify-content: center; color: #777;">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.30596698663!2d-74.25986790924793!3d40.697149413862214!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sin!4v1645455874284!5m2!1sen!2sin"
        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
</div>

<?php include 'includes/footer.php'; ?>