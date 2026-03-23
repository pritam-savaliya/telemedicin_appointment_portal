<?php include 'includes/header.php'; ?>

<section class="hero section fade-in" style="min-height: 90vh; display: flex; align-items: center; position: relative; overflow: hidden; padding-top: 2rem;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 4rem; align-items: center;">
            <div class="hero-content">
                <span class="badge badge-info" style="margin-bottom: 1.5rem; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);">
                    <i class="fas fa-shield-halved"></i> TRUSTED BY 10,000+ PATIENTS
                </span>
                <h1 style="font-size: clamp(3rem, 6vw, 5rem); margin-bottom: 2rem; line-height: 1.1;">
                    Healthcare That <br>
                    <span style="background: linear-gradient(to right, #6366f1, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Comes to You</span>
                </h1>
                <p style="font-size: 1.25rem; color: var(--text-muted); margin-bottom: 3rem; max-width: 550px; line-height: 1.8;">
                    Connect with 500+ verified medical specialists in seconds. Secure, private, and instant consultations from the comfort of your home.
                </p>
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="auth/login.php" class="btn btn-primary" style="padding: 1.2rem 2.8rem; font-size: 1.1rem;">
                            Get Started Now <i class="fas fa-arrow-right" style="font-size: 0.9rem; margin-left: 5px;"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $home_url; ?>" class="btn btn-primary" style="padding: 1.2rem 2.8rem; font-size: 1.1rem;">
                            Go to Dashboard <i class="fas fa-arrow-right" style="font-size: 0.9rem; margin-left: 5px;"></i>
                        </a>
                    <?php endif; ?>
                    <a href="about.php" class="btn btn-secondary" style="padding: 1.2rem 2.5rem; font-size: 1.1rem;">
                        How it Works
                    </a>
                </div>
            </div>

            <div class="hero-visual" style="position: relative;">
                <div class="glass-card" style="padding: 1.5rem; border-radius: 2rem; background: rgba(255,255,255,0.05); position: relative; z-index: 2;">
                    <div style="background: var(--bg-dark); border-radius: 1.5rem; overflow: hidden; border: 1px solid var(--border-glass);">
                        <div style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass);">
                            <div style="display: flex; gap: 8px;">
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #f43f5e;"></div>
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b;"></div>
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></div>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-dim);">Live Consultation Dashboard</span>
                        </div>
                        <div style="padding: 2rem;">
                            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem;">
                                <div style="position: relative;">
                                    <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=150&q=80" style="width: 70px; height: 70px; border-radius: 50%; border: 3px solid var(--primary);" onerror="this.src='https://ui-avatars.com/api/?name=Dr+Sarah+Chen&background=6366f1&color=fff&size=150'">
                                    <div style="position: absolute; bottom: 5px; right: 5px; width: 15px; height: 15px; background: var(--success); border-radius: 50%; border: 3px solid var(--bg-dark);"></div>
                                </div>
                                <div>
                                    <h4 style="margin-bottom: 5px;">Dr. Sarah Chen</h4>
                                    <div class="badge badge-success" style="font-size: 0.6rem; padding: 2px 8px;">Cardiologist</div>
                                </div>
                            </div>
                            <div style="display: grid; gap: 1rem;">
                                <div style="height: 12px; width: 100%; background: rgba(255,255,255,0.05); border-radius: 6px;"></div>
                                <div style="height: 12px; width: 80%; background: rgba(255,255,255,0.05); border-radius: 6px;"></div>
                                <div style="height: 45px; width: 100%; background: var(--primary); border-radius: 12px; margin-top: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">
                                    Join Video Call
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="position: absolute; top: -10%; right: -10%; width: 300px; height: 300px; background: var(--primary); filter: blur(100px); opacity: 0.3; z-index: 1;"></div>
                <div style="position: absolute; bottom: -10%; left: -10%; width: 250px; height: 250px; background: var(--secondary); filter: blur(100px); opacity: 0.2; z-index: 1;"></div>
            </div>
        </div>
    </div>
</section>

<section style="background: rgba(15, 23, 42, 0.5); border-top: 1px solid var(--border-glass); border-bottom: 1px solid var(--border-glass); padding: 3rem 0;">
    <div class="container" style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 3rem;">
        <div style="text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 5px;">10k+</h2>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Happy Patients</p>
        </div>
        <div style="text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 5px;">500+</h2>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Top Specialists</p>
        </div>
        <div style="text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 5px;">25+</h2>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Specialties</p>
        </div>
        <div style="text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 5px;">4.9</h2>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Avg Rating</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 5rem;" class="fade-in">
            <h2 style="font-size: 3rem; margin-bottom: 1.5rem;">The Future of <span style="color: var(--secondary);">Healthcare</span></h2>
            <p style="color: var(--text-muted); max-width: 650px; margin: 0 auto; font-size: 1.1rem;">We've built a platform that removes the barriers between you and world-class medical experts.</p>
        </div>

        <div class="grid-3">
            <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--secondary); margin-bottom: 2rem;">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>HD Video Consultations</h3>
                    <p style="color: var(--text-muted); margin-top: 1rem; margin-bottom: 2rem;">Experience face-to-face medical care from anywhere in the world with encrypted video technology.</p>
                </div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'patient/book_appointment.php' : 'auth/login.php'; ?>" class="btn btn-secondary" style="width: 100%;">Launch Session</a>
            </div>
            <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success); margin-bottom: 2rem;">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3>Instant E-Prescriptions</h3>
                    <p style="color: var(--text-muted); margin-top: 1rem; margin-bottom: 2rem;">Receive digital prescriptions instantly after your call, allowing you to order medicines with one click.</p>
                </div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'patient/view_prescription.php' : 'auth/login.php'; ?>" class="btn btn-secondary" style="width: 100%;">View Records</a>
            </div>
            <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning); margin-bottom: 2rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Verified Specialists</h3>
                    <p style="color: var(--text-muted); margin-top: 1rem; margin-bottom: 2rem;">Our doctors undergo a rigorous 5-step verification process to ensure you only get the best care.</p>
                </div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'patient/book_appointment.php' : 'auth/login.php'; ?>" class="btn btn-secondary" style="width: 100%;">Find Specialist</a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="container" style="margin-bottom: 6rem;">
    <div class="glass-card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%); text-align: center; padding: 5rem 2rem; overflow: hidden; position: relative;">
        <div style="position: relative; z-index: 2;">
            <h2 style="font-size: 3rem; margin-bottom: 1.5rem;">Ready to Prioritize Your Health?</h2>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 3rem; font-size: 1.2rem;">Join MedConnect today and get your first consultation scheduled in minutes.</p>
            <a href="<?php echo isset($_SESSION['user_id']) ? $home_url : 'auth/login.php'; ?>" class="btn btn-primary" style="padding: 1.2rem 3.5rem; font-size: 1.15rem;">
                <?php echo isset($_SESSION['user_id']) ? 'Go to Dashboard' : 'Sign In Now'; ?>
            </a>
        </div>
        <!-- Decorative Background Symbols -->
        <i class="fas fa-stethoscope" style="position: absolute; top: -20px; right: -20px; font-size: 15rem; opacity: 0.03; transform: rotate(-15deg);"></i>
        <i class="fas fa-heart-pulse" style="position: absolute; bottom: -20px; left: -20px; font-size: 15rem; opacity: 0.03; transform: rotate(15deg);"></i>
    </div>
</section>

<?php include 'includes/footer.php'; ?>