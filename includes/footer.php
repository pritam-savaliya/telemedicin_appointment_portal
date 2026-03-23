<footer style="background: var(--bg-sidebar); border-top: 1px solid var(--border-glass); padding: 5rem 0 2rem; position: relative; overflow: hidden;">
    <div style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; height: 2px; background: linear-gradient(90deg, transparent, var(--primary), var(--secondary), transparent); opacity: 0.3;"></div>

    <div class="container" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 4rem;">
        <div class="footer-section">
            <div class="logo" style="font-size: 1.8rem; margin-bottom: 1.5rem;">
                <i class="fas fa-heart-pulse"></i> <span>MedConnect</span>
            </div>
            <p style="color: var(--text-dim); line-height: 1.8; margin-bottom: 2rem; font-size: 0.95rem;">
                Redefining the standard of remote healthcare with secure, high-integrity medical consulting and digital health management for the modern world.
            </p>
            <div style="display: flex; gap: 1rem;">
                <a href="https://www.linkedin.com/" target="_blank" class="btn btn-secondary" style="padding: 0.8rem; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://x.com/" target="_blank" class="btn btn-secondary" style="padding: 0.8rem; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-x-twitter"></i></a>
                <a href="https://github.com/" target="_blank" class="btn btn-secondary" style="padding: 0.8rem; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-github"></i></a>
            </div>
        </div>

        <div class="footer-section">
            <h4 style="color: var(--text-main); margin-bottom: 1.5rem; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; font-weight: 800;">Platform</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 1rem;"><a href="<?php echo BASE_URL; ?>index.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dim)'">Home Interface</a></li>
                <li style="margin-bottom: 1rem;"><a href="<?php echo BASE_URL; ?>patient/book_appointment.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dim)'">Find Specialist</a></li>
                <li style="margin-bottom: 1rem;"><a href="<?php echo BASE_URL; ?>about.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dim)'">Clinical Network</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4 style="color: var(--text-main); margin-bottom: 1.5rem; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; font-weight: 800;">Resources</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 1rem;"><a href="<?php echo BASE_URL; ?>about.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dim)'">Medical Blog</a></li>
                <li style="margin-bottom: 1rem;"><a href="<?php echo BASE_URL; ?>about.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dim)'">Privacy Vault</a></li>
                <li style="margin-bottom: 1rem;"><a href="<?php echo BASE_URL; ?>contact.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dim)'">Help Center Hub</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4 style="color: var(--text-main); margin-bottom: 1.5rem; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; font-weight: 800;">Newsletter</h4>
            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 1.5rem;">Secure clinical updates and health insights.</p>
            <div style="position: relative;">
                <input type="email" id="newsletter-email" placeholder="name@email.com" class="form-control" style="padding: 1rem; padding-right: 4rem;">
                <button id="subscribe-btn" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div id="newsletter-message" style="margin-top: 10px; font-size: 0.8rem; height: 1.2rem;"></div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 5rem; padding-top: 2rem; border-top: 1px solid var(--border-glass); color: var(--text-muted); font-size: 0.8rem;">
        <p>&copy; <?php echo date("Y"); ?> <span style="font-weight: 800; color: var(--text-main);">MedConnect</span>. Engineered for Digital Health.</p>
    </div>
</footer>

<script>
    document.getElementById('subscribe-btn').addEventListener('click', function(e) {
        e.preventDefault();
        const emailInput = document.getElementById('newsletter-email');
        const messageDiv = document.getElementById('newsletter-message');
        const btn = this;
        const email = emailInput.value.trim();

        if (!email) {
            messageDiv.style.color = '#ef4444';
            messageDiv.innerText = 'Email is required.';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        messageDiv.innerText = '';

        const formData = new FormData();
        formData.append('email', email);

        fetch('<?php echo BASE_URL; ?>api/subscribe_newsletter.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            
            if (data.status === 'success') {
                messageDiv.style.color = '#10b981';
                emailInput.value = '';
            } else if (data.status === 'info') {
                messageDiv.style.color = '#3b82f6';
            } else {
                messageDiv.style.color = '#ef4444';
            }
            messageDiv.innerText = data.message;
            
            setTimeout(() => {
                messageDiv.innerText = '';
            }, 5000);
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            messageDiv.style.color = '#ef4444';
            messageDiv.innerText = 'Network error. Please try again.';
        });
    });
</script>
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>