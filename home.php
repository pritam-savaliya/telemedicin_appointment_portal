<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Your Health, Our Priority</h1>
        <p>Experience the future of healthcare. Consult with top specialists via video call, book in-person
            appointments, or order medicines - all from the comfort of your home.</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'patient'): ?>
                <a href="book_appointment.php" class="btn btn-primary">Book Consultation</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Get Started</a>
            <?php endif; ?>
            <a href="about.php" class="btn btn-primary">Learn More</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <h2 class="section-title">Comprehensive Healthcare Services</h2>
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-video feature-icon"></i>
            <h3>Video Consultation</h3>
            <p>Connect with doctors instantly via secure video calls. No waiting rooms, just quality care.</p>
            <a href="book_appointment.php" style="color: var(--primary-color); font-weight: 600;">Consult Now &rarr;</a>
        </div>
        <div class="feature-card">
            <i class="fas fa-calendar-alt feature-icon"></i>
            <h3>Clinic Visits</h3>
            <p>Book appointments at preferred clinics or hospitals with your favorite specialists.</p>
            <a href="book_appointment.php" style="color: var(--primary-color); font-weight: 600;">Book Visit &rarr;</a>
        </div>
        <div class="feature-card">
            <i class="fas fa-pills feature-icon"></i>
            <h3>Medicine Delivery</h3>
            <p>Upload your prescription and get medicines delivered right to your doorstep fast.</p>
            <a href="order_medicine.php" style="color: var(--primary-color); font-weight: 600;">Order Now &rarr;</a>
        </div>
    </div>
</section>

<!-- Specialties Section -->
<section class="section" style="background-color: var(--bg-white);">
    <h2 class="section-title">Our Top Specialties</h2>
    <div class="specialties-grid">
        <div class="specialty-item">
            <i class="fas fa-heartbeat" style="color: #e91e63;"></i>
            <h4>Cardiology</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Heart & Blood</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-baby" style="color: #03a9f4;"></i>
            <h4>Pediatrics</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Child Care</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-allergies" style="color: #ff9800;"></i>
            <h4>Dermatology</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Skin Specialists</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-brain" style="color: #9c27b0;"></i>
            <h4>Psychiatry</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Mental Health</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-tooth" style="color: #009688;"></i>
            <h4>Dentistry</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Dental Care</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-bone" style="color: #795548;"></i>
            <h4>Orthopedics</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Bone & Joint</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-eye" style="color: #3f51b5;"></i>
            <h4>Ophthalmology</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Eye Care</span>
        </div>
        <div class="specialty-item">
            <i class="fas fa-lungs" style="color: #f44336;"></i>
            <h4>Pulmonology</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Lung Health</span>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section" style="padding-bottom: 8rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 50px; align-items: center; justify-content: center;">
        <div style="flex: 1; min-width: 300px; max-width: 600px;">
            <img src="https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=800&q=80"
                alt="Medical Team"
                style="border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); width: 100%; height: auto;">
        </div>
        <div
            style="flex: 1; min-width: 300px; background: rgba(255, 255, 255, 0.95); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); backdrop-filter: blur(10px);">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--secondary-color);">Why Choose TeleMed?
            </h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">We bridge the gap between patients and healthcare
                providers, ensuring you get the best medical attention whenever you need it.</p>

            <ul style="list-style: none;">
                <li style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 15px;">
                    <div
                        style="background: rgba(0, 184, 148, 0.1); padding: 10px; border-radius: 50%; color: var(--success-color);">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.2rem;">Verified Doctors</h4>
                        <span style="font-size: 0.9rem; color: var(--text-muted);">100% certified and experienced
                            specialists</span>
                    </div>
                </li>
                <li style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 15px;">
                    <div
                        style="background: rgba(0, 184, 148, 0.1); padding: 10px; border-radius: 50%; color: var(--success-color);">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.2rem;">Secure & Private</h4>
                        <span style="font-size: 0.9rem; color: var(--text-muted);">End-to-end encryption for all
                            consultations</span>
                    </div>
                </li>
                <li style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 15px;">
                    <div
                        style="background: rgba(0, 184, 148, 0.1); padding: 10px; border-radius: 50%; color: var(--success-color);">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.2rem;">24/7 Support</h4>
                        <span style="font-size: 0.9rem; color: var(--text-muted);">Always available to assist you</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>