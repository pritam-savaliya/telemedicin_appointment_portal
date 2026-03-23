<?php include 'includes/header.php'; ?>

<div class="container fade-in" style="padding-top: 5rem; padding-bottom: 5rem;">
    <!-- Hero Section -->
    <div style="text-align: center; margin-bottom: 5rem;">
        <div class="badge badge-info" style="margin-bottom: 1.5rem;"><i class="fas fa-dna"></i> OUR ORIGIN STORY</div>
        <h1 style="font-size: 3.5rem; margin-bottom: 1rem;">Engineering the <span style="color: var(--secondary);">Future</span> of Care.</h1>
        <p style="color: var(--text-muted); font-size: 1.3rem; max-width: 800px; margin: 0 auto; line-height: 1.7;">
            MedConnect was born out of a simple mission: to bridge the gap between world-class medical expertise and patients who need it most, regardless of geography.
        </p>
    </div>

    <!-- Vision/Mission Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; margin-bottom: 8rem;">
        <div class="glass-card" style="padding: 0; overflow: hidden; position: relative;">
            <img src="https://images.unsplash.com/photo-1576091160550-217359f4ecf8?auto=format&fit=crop&w=1200" style="width: 100%; height: 500px; object-fit: cover; opacity: 0.8;" onerror="this.src='https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200'">
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 3rem; background: linear-gradient(to top, var(--bg-card), transparent);">
                <h2 style="font-size: 2.2rem; margin-bottom: 1rem;">Clinical Excellence</h2>
                <p style="color: var(--text-muted);">Empowering doctors with high-fidelity tools to deliver precise care remotely.</p>
            </div>
        </div>
        
        <div style="display: grid; gap: 3rem;">
            <div>
                <h3 style="color: var(--primary); font-size: 1.5rem; margin-bottom: 1rem;"><i class="fas fa-bullseye" style="margin-right: 10px;"></i> Our Mission</h3>
                <p style="color: var(--text-dim); line-height: 1.8; font-size: 1.1rem;">
                    To democratize premium healthcare by providing a secure, intelligent platform where patients can connect with verified specialists in under 60 seconds.
                </p>
            </div>
            <div class="glass-card" style="border-left: 4px solid var(--secondary);">
                <h3 style="color: var(--secondary); font-size: 1.5rem; margin-bottom: 1rem;"><i class="fas fa-eye" style="margin-right: 10px;"></i> Our Vision</h3>
                <p style="color: var(--text-dim); line-height: 1.8;">
                    A world where critical medical advice is not a luxury, but a high-speed utility available to every human being with a digital connection.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="stats-grid" style="margin-bottom: 8rem;">
        <div class="glass-card stat-card" style="text-align: center; border-radius: 24px;">
            <h2 style="font-size: 3rem; color: var(--primary); margin-bottom: 0.5rem;">500+</h2>
            <span style="color: var(--text-dim); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px;">Board Certified</span>
        </div>
        <div class="glass-card stat-card" style="text-align: center; border-radius: 24px;">
            <h2 style="font-size: 3rem; color: var(--secondary); margin-bottom: 0.5rem;">10k+</h2>
            <span style="color: var(--text-dim); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px;">Lives Impacted</span>
        </div>
        <div class="glass-card stat-card" style="text-align: center; border-radius: 24px;">
            <h2 style="font-size: 3rem; color: var(--success); margin-bottom: 0.5rem;">99.9%</h2>
            <span style="color: var(--text-dim); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px;">Uptime SLA</span>
        </div>
        <div class="glass-card stat-card" style="text-align: center; border-radius: 24px;">
            <h2 style="font-size: 3rem; color: var(--accent); margin-bottom: 0.5rem;">24/7</h2>
            <span style="color: var(--text-dim); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px;">Critical Support</span>
        </div>
    </div>

    <!-- Medical Board -->
    <div style="margin-bottom: 8rem; position: relative;">
        <div style="text-align: center; margin-bottom: 5rem;">
            <div class="badge badge-success" style="margin-bottom: 1.5rem;"><i class="fas fa-certificate"></i> VERIFIED EXPERTISE</div>
            <h2 style="font-size: 3rem; margin-bottom: 1rem;">The Medical Advisory Board</h2>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">Our elite committee of healthcare leaders dedicated to maintaining the highest clinical standards across the MedConnect ecosystem.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem;">
            <?php
            // Randomized clinicians for a dynamic platform feel
            $team_sql = "SELECT u.id, u.fullname, u.profile_pic, dp.specialty, dp.bio 
                         FROM users u 
                         LEFT JOIN doctor_profiles dp ON u.id = dp.user_id 
                         WHERE u.role = 'doctor' 
                         AND u.is_approved = 1
                         ORDER BY RAND() LIMIT 4";
            $team_result = $conn->query($team_sql);

            if ($team_result->num_rows > 0) {
                while ($doc = $team_result->fetch_assoc()) {
                    $pic_url = !empty($doc['profile_pic']) && $doc['profile_pic'] != 'default_user.png' && file_exists('assets/uploads/profile_pics/' . $doc['profile_pic']) 
                               ? BASE_URL . 'assets/uploads/profile_pics/' . $doc['profile_pic'] 
                               : 'https://ui-avatars.com/api/?name=' . urlencode($doc['fullname']) . '&background=c084fc&color=fff&size=200';
                    ?>
                    <div class="glass-card" style="padding: 0; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid var(--border-glass);" 
                         onmouseover="this.style.transform='translateY(-15px)'; this.style.borderColor='var(--secondary)'; this.style.boxShadow='0 25px 50px -12px rgba(192, 132, 252, 0.25)';" 
                         onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-glass)'; this.style.boxShadow='none';">
                        
                        <div style="height: 12px; background: linear-gradient(to right, var(--primary), var(--secondary));"></div>
                        
                        <div style="padding: 3rem 2rem 2.5rem;">
                            <div style="position: relative; width: 140px; height: 140px; margin: 0 auto 2rem;">
                                <img src="<?php echo $pic_url; ?>" style="width: 100%; height: 100%; border-radius: 40px; object-fit: cover; border: 3px solid var(--bg-card); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);" 
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($doc['fullname']); ?>&background=c084fc&color=fff&size=200'">
                                <div style="position: absolute; bottom: -5px; right: -5px; width: 32px; height: 32px; background: var(--secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid var(--bg-card); color: white; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>

                            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--text-main);">Dr. <?php echo str_replace('Dr. ', '', $doc['fullname']); ?></h3>
                            <div style="color: var(--secondary); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.5rem;">
                                <?php echo !empty($doc['specialty']) ? $doc['specialty'] : 'Platform Specialist'; ?>
                            </div>
                            
                            <p style="color: var(--text-dim); font-size: 0.95rem; line-height: 1.7; margin-bottom: 2rem; min-height: 100px;">
                                <?php 
                                $bio_text = $doc['bio'] ? $doc['bio'] : 'A dedicated healthcare professional committed to advancing digital clinical practices and providing high-fidelity remote patient care.';
                                echo strlen($bio_text) > 130 ? substr($bio_text, 0, 127) . "..." : $bio_text;
                                ?>
                            </p>

                            <a href="<?php echo BASE_URL; ?>patient/view_doctor_profile.php?id=<?php echo $doc['id']; ?>" 
                               class="btn btn-primary" 
                               style="width: 100%; padding: 1rem; font-weight: 700; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.2);">
                                Dossier <i class="fas fa-arrow-right-long" style="font-size: 0.8rem; opacity: 0.5;"></i>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='text-align: center; color: var(--text-muted); width: 100%;'>Board members currently in clinical session.</p>";
            }
            ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>