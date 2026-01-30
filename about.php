<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div
    style="background: url('https://source.unsplash.com/1600x600/?hospital,medical') no-repeat center center/cover; padding: 100px 5%; text-align: center; color: var(--white); position: relative;">
    <div style="background: rgba(0,0,0,0.6); position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></div>
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 3.5rem; margin-bottom: 20px;">About TeleMed</h1>
        <p style="font-size: 1.5rem;">Transforming Healthcare with Technology & Compassion</p>
    </div>
</div>

<!-- Mission & Vision -->
<section class="section">
    <div style="display: flex; flex-wrap: wrap; gap: 40px;">
        <div style="flex: 1; min-width: 300px;">
            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Our Mission</h2>
            <p style="font-size: 1.1rem; color: var(--secondary-color);">To make quality healthcare accessible,
                affordable, and convenient for everyone, everywhere. We believe that distance should not be a barrier to
                receiving the best medical advice and treatment.</p>
        </div>
        <div style="flex: 1; min-width: 300px;">
            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Our Vision</h2>
            <p style="font-size: 1.1rem; color: var(--secondary-color);">A world where everyone has instant access to
                top-tier medical professionals from the comfort of their homes, leading to a healthier and happier
                society.</p>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="section" style="background: var(--primary-color); color: var(--white); text-align: center;">
    <div class="features-grid">
        <div>
            <h2 style="font-size: 3rem;">500+</h2>
            <p>Certified Doctors</p>
        </div>
        <div>
            <h2 style="font-size: 3rem;">10k+</h2>
            <p>Happy Patients</p>
        </div>
        <div>
            <h2 style="font-size: 3rem;">24/7</h2>
            <p>Support Available</p>
        </div>
        <div>
            <h2 style="font-size: 3rem;">50+</h2>
            <p>Specialties</p>
        </div>
    </div>
</section>

<!-- Our Team -->
<section class="section" style="text-align: center;">
    <h2 class="section-title">Meet Our Medical Board</h2>
    <div class="features-grid">
        <div class="feature-card">
            <img src="https://source.unsplash.com/200x200/?doctor,man" alt="Dr. Smith"
                style="border-radius: 50%; margin-bottom: 15px; width: 150px; height: 150px; object-fit: cover;">
            <h3>Dr. John Smith</h3>
            <p style="color: var(--primary-color);">Chief Medical Officer</p>
        </div>
        <div class="feature-card">
            <img src="https://source.unsplash.com/200x200/?doctor,woman" alt="Dr. Sarah"
                style="border-radius: 50%; margin-bottom: 15px; width: 150px; height: 150px; object-fit: cover;">
            <h3>Dr. Sarah Johnson</h3>
            <p style="color: var(--primary-color);">Head of Pediatrics</p>
        </div>
        <div class="feature-card">
            <img src="https://source.unsplash.com/200x200/?doctor,man,glasses" alt="Dr. David"
                style="border-radius: 50%; margin-bottom: 15px; width: 150px; height: 150px; object-fit: cover;">
            <h3>Dr. David Lee</h3>
            <p style="color: var(--primary-color);">Senior Cardiologist</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>