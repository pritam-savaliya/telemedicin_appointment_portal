<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<div
    style="background: linear-gradient(135deg, var(--secondary-color), #2d3436); color: white; padding: 100px 0 60px; text-align: center;">
    <div class="container">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; color: white;">Contact Us</h1>
        <p style="font-size: 1.2rem; opacity: 0.8; max-width: 600px; margin: 0 auto;">We'd love to hear from you. Reach
            out to us for any queries or support.</p>
    </div>
</div>

<?php
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // In a real app, we would save to DB or send email
    // For now, just show success
    $msg = "<div class='alert-success'>Message sent successfully! We will get back to you soon.</div>";
}
?>

<section class="section" style="margin-top: -30px;">
    <div class="container">
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-wrap: wrap;">
            <!-- Contact Info Side -->
            <div style="flex: 1; min-width: 300px; background: var(--primary-color); padding: 3rem; color: white;">
                <h3 style="color: white; margin-bottom: 2rem; font-size: 1.8rem;">Contact Information</h3>

                <div style="margin-bottom: 2rem; display: flex; gap: 15px;">
                    <i class="fas fa-map-marker-alt" style="font-size: 1.2rem; margin-top: 5px;"></i>
                    <p style="opacity: 0.9;">123 Medical Plaza, Health Street,<br>New York, NY 10001</p>
                </div>

                <div style="margin-bottom: 2rem; display: flex; gap: 15px;">
                    <i class="fas fa-phone" style="font-size: 1.2rem; margin-top: 5px;"></i>
                    <p style="opacity: 0.9;">+1 (555) 123-4567</p>
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
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" class="form-control" placeholder="Your Email" required>
                        </div>
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
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Send Message <i
                            class="fas fa-paper-plane" style="margin-left: 8px;"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<div style="width: 100%; height: 400px; background: #eee;">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.30596698663!2d-74.25986790924793!3d40.697149413862214!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20USA!5e0!3m2!1sen!2sin!4v1645455874284!5m2!1sen!2sin"
        width="100%" height="100%" style="border:0; filter: grayscale(100%);" allowfullscreen=""
        loading="lazy"></iframe>
</div>

<?php include 'includes/footer.php'; ?>