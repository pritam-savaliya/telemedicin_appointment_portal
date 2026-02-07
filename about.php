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
        <h2 class="section-title" style="text-align: center; margin-bottom: 20px;">Meet Our Medical Board</h2>
        <p
            style="text-align: center; color: var(--text-muted); margin-bottom: 50px; max-width: 600px; margin-left: auto; margin-right: auto;">
            A diverse team of specialists dedicated to providing world-class healthcare with empathy and expertise.
        </p>

        <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <?php
            // Fetch 6 Doctors with Profile Details
            $team_sql = "SELECT u.id, u.fullname, u.profile_pic, u.gender, dp.specialty, dp.qualification, dp.experience, dp.bio 
                         FROM users u 
                         LEFT JOIN doctor_profiles dp ON u.id = dp.user_id 
                         WHERE u.role = 'doctor' 
                         ORDER BY u.id ASC 
                         LIMIT 6";
            $team_result = $conn->query($team_sql);

            if ($team_result->num_rows > 0) {
                while ($doc = $team_result->fetch_assoc()) {
                    $pic = !empty($doc['profile_pic']) ? BASE_URL . $doc['profile_pic'] : 'https://via.placeholder.com/150';
                    $specialty = $doc['specialty'] ?? 'General Physician';
                    $qual = $doc['qualification'] ?? 'MBBS';
                    $exp = $doc['experience'] ?? 'Unknown';
                    $gender = $doc['gender'] ?? 'Not Specified';
                    $bio = $doc['bio'] ?? '';

                    // Truncate bio if too long
                    $short_bio = strlen($bio) > 80 ? substr($bio, 0, 77) . '...' : $bio;
                    ?>
                    <div class="feature-card"
                        style="padding: 0; overflow: hidden; text-align: left; display: flex; flex-direction: column; height: 100%;">
                        <div
                            style="padding: 30px; text-align: center; background: linear-gradient(to bottom, var(--bg-body), white);">
                            <img src="<?php echo $pic; ?>" alt="<?php echo $doc['fullname']; ?>"
                                style="border-radius: 50%; width: 120px; height: 120px; object-fit: cover; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 15px;">
                            <h3 style="margin-bottom: 5px; font-size: 1.4rem; color: var(--text-color);">
                                <?php echo $doc['fullname']; ?></h3>
                            <span
                                style="background: rgba(108, 92, 231, 0.1); color: var(--primary-color); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                <?php echo $specialty; ?>
                            </span>
                        </div>

                        <div style="padding: 25px; flex-grow: 1; border-top: 1px solid #f0f0f0;">
                            <div style="margin-bottom: 15px; font-size: 0.95rem; color: var(--text-muted); line-height: 1.6;">
                                <?php echo $short_bio; ?>
                            </div>

                            <div
                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem; margin-bottom: 20px;">
                                <div>
                                    <i class="fas fa-graduation-cap" style="color: var(--secondary-color); width: 20px;"></i>
                                    <span style="color: var(--text-main); font-weight: 500;"><?php echo $qual; ?></span>
                                </div>
                                <div>
                                    <i class="fas fa-briefcase" style="color: var(--secondary-color); width: 20px;"></i>
                                    <span style="color: var(--text-main); font-weight: 500;"><?php echo $exp; ?></span>
                                </div>
                                <div>
                                    <i class="fas fa-venus-mars" style="color: var(--secondary-color); width: 20px;"></i>
                                    <span style="color: var(--text-main); font-weight: 500;"><?php echo $gender; ?></span>
                                </div>
                            </div>

                            <div style="text-align: center;">
                                <a href="<?php echo BASE_URL; ?>patient/view_doctor_profile.php?id=<?php echo $doc['id']; ?>"
                                    class="btn btn-outline" style="width: 100%; border-radius: 8px;">View Full Profile</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p style="text-align:center; grid-column: 1/-1;">No doctors found.</p>';
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>