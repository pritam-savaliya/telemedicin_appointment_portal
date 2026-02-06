<footer
    style="background: var(--bg-footer); color: var(--text-main); padding: 4rem 0 2rem; border-top: 1px solid var(--border-color); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
    <div class="container"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
        <div class="footer-section">
            <h4 style="color: var(--text-main); margin-bottom: 20px; font-size: 1.5rem;">TeleMed</h4>
            <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">
                Your trusted partner for online medical consultations. Connect with top-rated doctors anywhere, anytime.
            </p>
            <div style="display: flex; gap: 15px;">
                <a href="https://www.facebook.com" target="_blank"
                    style="color: var(--text-muted); font-size: 1.2rem; transition: color 0.3s;"><i
                        class="fab fa-facebook"></i></a>
                <a href="https://x.com" target="_blank"
                    style="color: var(--text-muted); font-size: 1.2rem; transition: color 0.3s;"><i
                        class="fab fa-x-twitter"></i></a>
                <a href="https://www.instagram.com" target="_blank"
                    style="color: var(--text-muted); font-size: 1.2rem; transition: color 0.3s;"><i
                        class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h4 style="color: var(--text-main); margin-bottom: 20px;">Quick Links</h4>
            <ul style="color: var(--text-muted); list-style: none; padding-left: 0;">
                <li style="margin-bottom: 10px;"><a href="<?php echo BASE_URL; ?>index.php"
                        style="color: inherit;">Home</a></li>
                <li style="margin-bottom: 10px;"><a href="<?php echo BASE_URL; ?>about.php"
                        style="color: inherit;">About Us</a></li>
                <li style="margin-bottom: 10px;"><a href="<?php echo BASE_URL; ?>contact.php"
                        style="color: inherit;">Contact Support</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4 style="color: var(--text-main); margin-bottom: 20px;">Contact Us</h4>
            <ul style="color: var(--text-muted); list-style: none; padding-left: 0;">
                <li style="margin-bottom: 10px;"><i class="fas fa-envelope" style="margin-right: 10px;"></i>
                    support@telemed.com</li>
                <li style="margin-bottom: 10px;"><i class="fas fa-phone" style="margin-right: 10px;"></i> +1 234 567 890
                </li>
            </ul>
        </div>
    </div>
    <div
        style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.9rem;">
        <p>&copy; <?php echo date("Y"); ?> TeleMed. All Rights Reserved.</p>
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>

</html>