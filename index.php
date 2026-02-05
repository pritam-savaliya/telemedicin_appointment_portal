<?php include 'includes/header.php'; ?>

<!-- Toast for dynamically triggered JS messages -->
<div id="toast-notification" class="toast-notification"></div>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 60px;">
            <div style="flex: 1.2; min-width: 320px;" class="animate-up">
                <span class="hero-tag">New Standard in Care</span>
                <h1>Your Health, <br> Our <span>Expertise</span></h1>
                <p>Connect with top-tier medical specialists from the comfort of your home. Secure, reliable, and
                    instant healthcare at your fingertips.</p>

                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'patient'): ?>
                        <a href="<?php echo BASE_URL; ?>patient/book_appointment.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary">
                            Get Started <i class="fas fa-rocket"></i>
                        </a>
                    <?php endif; ?>
                    <a href="about.php" class="btn btn-secondary">
                        Learn More <i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i>
                    </a>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; position: relative;" class="animate-up">
                <div
                    style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: var(--success); border-radius: 50%; opacity: 0.1; filter: blur(30px);">
                </div>
                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80"
                    alt="Healthcare Professional"
                    style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); transition: transform 0.5s ease;"
                    onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section" style="background: var(--bg-body);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 4rem;" class="animate-up">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Why Choose TeleMed?</h2>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">We combine cutting-edge technology
                with compassionate medical care to provide the best experience.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div class="card animate-up" style="animation-delay: 0.1s;">
                <div
                    style="width: 70px; height: 70px; background: var(--primary-soft); color: var(--primary); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 2rem;">
                    <i class="fas fa-video"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Video Consultation</h3>
                <p style="color: var(--text-muted);">Secure HD video consultations with top doctors without leaving your
                    home.</p>
            </div>

            <div class="card animate-up" style="animation-delay: 0.2s;">
                <div
                    style="width: 70px; height: 70px; background: rgba(16, 185, 129, 0.1); color: var(--success); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 2rem;">
                    <i class="fas fa-clinic-medical"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Clinic Visits</h3>
                <p style="color: var(--text-muted);">Prefer in-person care? Book sessions at thousands of verified
                    partner clinics.</p>
            </div>

            <div class="card animate-up" style="animation-delay: 0.3s;">
                <div
                    style="width: 70px; height: 70px; background: rgba(244, 63, 94, 0.1); color: var(--accent); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 2rem;">
                    <i class="fas fa-pills"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">E-Prescriptions</h3>
                <p style="color: var(--text-muted);">Receive digital prescriptions instantly and order medicines with
                    one click.</p>
            </div>
        </div>
    </div>
</section>

<!-- Specialties Section -->
<section class="section" style="background-color: var(--bg-card);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem;"
            class="animate-up">
            <div>
                <span class="hero-tag" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">Specialized
                    Care</span>
                <h2 style="font-size: 2.5rem; margin-top: 10px;">Our Top Specialties</h2>
            </div>
            <a href="#" style="color: var(--primary); font-weight: 700;">View All Specialties <i
                    class="fas fa-arrow-right"></i></a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
            <?php
            $specs = [
                ['name' => 'Cardiology', 'icon' => 'heartbeat', 'color' => '#f43f5e'],
                ['name' => 'Pediatrics', 'icon' => 'baby', 'color' => '#0ea5e9'],
                ['name' => 'Neurology', 'icon' => 'brain', 'color' => '#8b5cf6'],
                ['name' => 'Dermatology', 'icon' => 'sun', 'color' => '#f59e0b'],
                ['name' => 'Orthopedics', 'icon' => 'bone', 'color' => '#10b981']
            ];
            foreach ($specs as $index => $spec) {
                echo '<div class="card animate-up" style="animation-delay: ' . (0.1 * $index) . 's; padding: 2.5rem 1.5rem; text-align: center;">
                        <div style="width: 80px; height: 80px; background: ' . $spec['color'] . '15; color: ' . $spec['color'] . '; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem;">
                            <i class="fas fa-' . $spec['icon'] . '"></i>
                        </div>
                        <h4 style="margin-bottom: 0.5rem;">' . $spec['name'] . '</h4>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">CONSULT NOW</span>
                      </div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Section -->
<section class="section">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 80px;">
            <div style="flex: 1; min-width: 320px;" class="animate-up">
                <img src="https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=800&q=80"
                    alt="Innovation"
                    style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
            </div>
            <div style="flex: 1.25; min-width: 320px;" class="animate-up">
                <h2 style="font-size: 2.5rem; margin-bottom: 2rem;">A Medical Experience Like Never Before</h2>
                <div style="display: grid; gap: 30px;">
                    <div style="display: flex; gap: 20px;">
                        <div
                            style="width: 50px; height: 50px; background: var(--primary); color: white; border-radius: 14px; display: flex; flex-shrink: 0; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;">Guaranteed Privacy</h4>
                            <p style="color: var(--text-muted);">All consultations and medical records are end-to-end
                                encrypted and completely confidential.</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div
                            style="width: 50px; height: 50px; background: var(--success); color: white; border-radius: 14px; display: flex; flex-shrink: 0; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;">Top-Rated Specialists</h4>
                            <p style="color: var(--text-muted);">Our doctors undergo rigorous vetting and have an
                                average of 10+ years of clinical experience.</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div
                            style="width: 50px; height: 50px; background: var(--secondary); color: white; border-radius: 14px; display: flex; flex-shrink: 0; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;">Instant Connectivity</h4>
                            <p style="color: var(--text-muted);">Connect with a general physician within 15 minutes of
                                booking your appointment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>