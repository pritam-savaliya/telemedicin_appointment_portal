<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'appointment_booked'): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert-success" style="background: #E6FFFA; color: #00B894; border: 1px solid #00B894; border-radius: var(--radius-sm); padding: 15px; text-align: center;">
            <i class="fas fa-check-circle"></i> Appointment Booked Successfully!
        </div>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-content">
        <h1>Your Health, <br> Our Priority</h1>
        <p>Experience the future of healthcare. Consult with top specialists via video call, book in-person appointments, or order medicines - all from the comfort of your home.</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'patient'): ?>
                <a href="book_appointment.php" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Book Consultation</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Get Started <i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
            <a href="about.php" class="btn btn-secondary"><i class="fas fa-info-circle"></i> Learn More</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-video"></i>
                </div>
                <h3>Video Consultation</h3>
                <p style="color: var(--text-muted);">Connect with doctors instantly via secure video calls. No waiting rooms, just quality care.</p>
                <a href="book_appointment.php" class="btn btn-outline" style="margin-top: 20px; font-size: 0.9rem;">Consult Now</a>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clinic-medical"></i>
                </div>
                <h3>Clinic Visits</h3>
                <p style="color: var(--text-muted);">Book appointments at preferred clinics or hospitals with your favorite specialists nearby.</p>
                <a href="book_appointment.php" class="btn btn-outline" style="margin-top: 20px; font-size: 0.9rem;">Book Visit</a>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <h3>Medicine Delivery</h3>
                <p style="color: var(--text-muted);">Upload your prescription and get medicines delivered right to your doorstep within hours.</p>
                <a href="order_medicine.php" class="btn btn-outline" style="margin-top: 20px; font-size: 0.9rem;">Order Now</a>
            </div>
        </div>
    </div>
</section>

<!-- Specialties Section -->
<section class="section" style="background-color: white;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Our Top Specialties</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <?php
            $specialties = [
                ['name' => 'Cardiology', 'icon' => 'heartbeat', 'color' => '#e91e63'],
                ['name' => 'Pediatrics', 'icon' => 'baby', 'color' => '#03a9f4'],
                ['name' => 'Dermatology', 'icon' => 'allergies', 'color' => '#ff9800'],
                ['name' => 'Psychiatry', 'icon' => 'brain', 'color' => '#9c27b0'],
                ['name' => 'Dentistry', 'icon' => 'tooth', 'color' => '#009688'],
                ['name' => 'Orthopedics', 'icon' => 'bone', 'color' => '#795548'],
                ['name' => 'Ophthalmology', 'icon' => 'eye', 'color' => '#3f51b5'],
                ['name' => 'Pulmonology', 'icon' => 'lungs', 'color' => '#f44336']
            ];
            foreach ($specialties as $spec) {
                echo '<div class="card" style="text-align: center; padding: 1.5rem; transition: transform 0.3s; cursor: pointer;">
                        <i class="fas fa-' . $spec['icon'] . '" style="font-size: 2.5rem; color: ' . $spec['color'] . '; margin-bottom: 1rem;"></i>
                        <h4 style="margin-bottom: 0.5rem;">' . $spec['name'] . '</h4>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Specialists</span>
                      </div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 50px;">
            <div style="flex: 1; min-width: 300px;">
                <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80"
                    alt="Medical Team"
                    style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
            </div>
            <div style="flex: 1; min-width: 300px;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem;">Why Choose TeleMed?</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1.1rem;">
                    We bridge the gap between patients and healthcare providers, ensuring you get the best medical attention whenever you need it.
                </p>

                <ul style="display: flex; flex-direction: column; gap: 20px;">
                    <li style="display: flex; align-items: start; gap: 15px;">
                        <div style="background: rgba(0, 184, 148, 0.1); padding: 10px; border-radius: 50%; color: var(--success-color);">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 5px;">Verified Doctors</h4>
                            <p style="color: var(--text-muted); font-size: 0.95rem;">100% certified and experienced specialists from top hospitals.</p>
                        </div>
                    </li>
                    <li style="display: flex; align-items: start; gap: 15px;">
                        <div style="background: rgba(0, 184, 148, 0.1); padding: 10px; border-radius: 50%; color: var(--success-color);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 5px;">Secure & Private</h4>
                            <p style="color: var(--text-muted); font-size: 0.95rem;">End-to-end encryption for all your data and consultations.</p>
                        </div>
                    </li>
                    <li style="display: flex; align-items: start; gap: 15px;">
                        <div style="background: rgba(0, 184, 148, 0.1); padding: 10px; border-radius: 50%; color: var(--success-color);">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 5px;">24/7 Support</h4>
                            <p style="color: var(--text-muted); font-size: 0.95rem;">Always available to assist you with appointments and queries.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>