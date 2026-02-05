<?php include 'includes/header.php'; ?>

<div class="container section">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; color: var(--secondary-color); margin-bottom: 10px;">About TeleMed</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 700px; margin: 0 auto;">Transforming
            Healthcare with
            Technology & Compassion</p>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 60px; align-items: center;">
        <div style="flex: 1; min-width: 300px;">
            <img src="https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=800&q=80"
                alt="About Us" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
        </div>
        <div style="flex: 1; min-width: 300px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--primary-color);">Our Mission</h2>
            <p style="font-size: 1.1rem; color: var(--text-muted); line-height: 1.8; margin-bottom: 2rem;">
                To make quality healthcare accessible, affordable, and convenient for everyone, everywhere. We
                believe that distance should not be a barrier to receiving the best medical advice and treatment.
            </p>
            <div style="padding-left: 20px; border-left: 4px solid var(--accent-color);">
                <h4 style="font-size: 1.2rem; margin-bottom: 10px;">Our Vision</h4>
                <p style="color: var(--text-muted);">
                    A world where everyone has instant access to top-tier medical professionals from the comfort of
                    their homes, leading to a healthier and happier society.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<section class="section" style="background: var(--primary-color); color: white;">
    <div class="container">
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; text-align: center;">
            <div>
                <h2 style="font-size: 3.5rem; margin-bottom: 10px; color: white;">500+</h2>
                <p style="font-size: 1.1rem; opacity: 0.9;">Certified Doctors</p>
            </div>
            <div>
                <h2 style="font-size: 3.5rem; margin-bottom: 10px; color: white;">10k+</h2>
                <p style="font-size: 1.1rem; opacity: 0.9;">Happy Patients</p>
            </div>
            <div>
                <h2 style="font-size: 3.5rem; margin-bottom: 10px; color: white;">24/7</h2>
                <p style="font-size: 1.1rem; opacity: 0.9;">Support Available</p>
            </div>
            <div>
                <h2 style="font-size: 3.5rem; margin-bottom: 10px; color: white;">50+</h2>
                <p style="font-size: 1.1rem; opacity: 0.9;">Specialties</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title" style="text-align: center; margin-bottom: 50px;">Meet Our Medical Board</h2>
        <div class="features-grid">
            <div class="feature-card" style="padding: 30px;">
                <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=300&q=80"
                    alt="Dr. Smith"
                    style="border-radius: 50%; margin-bottom: 20px; width: 120px; height: 120px; object-fit: cover; border: 4px solid var(--bg-body);">
                <h3 style="margin-bottom: 5px;">Dr. John Smith</h3>
                <p style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">Chief Medical Officer</p>
            </div>
            <div class="feature-card" style="padding: 30px;">
                <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=300&q=80"
                    alt="Dr. Sarah"
                    style="border-radius: 50%; margin-bottom: 20px; width: 120px; height: 120px; object-fit: cover; border: 4px solid var(--bg-body);">
                <h3 style="margin-bottom: 5px;">Dr. Sarah Johnson</h3>
                <p style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">Head of Pediatrics</p>
            </div>
            <div class="feature-card" style="padding: 30px;">
                <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=300&q=80"
                    alt="Dr. David"
                    style="border-radius: 50%; margin-bottom: 20px; width: 120px; height: 120px; object-fit: cover; border: 4px solid var(--bg-body);">
                <h3 style="margin-bottom: 5px;">Dr. David Lee</h3>
                <p style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">Senior Cardiologist</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>