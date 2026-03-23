<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $address = $conn->real_escape_string($_POST['address']);

    if (!preg_match('/^[6-9][0-9]{9}$/', $phone) && !empty($phone)) {
        $message = "<div class='badge badge-danger' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-circle-exclamation'></i> Invalid phone architecture. Must be 10 digits.</div>";
    } else {
        $profile_pic_sql = "";
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);

            if (in_array(strtolower($filetype), $allowed)) {
                $new_filename = "user_" . $user_id . "_" . time() . "." . $filetype;
                $upload_path = "assets/uploads/profile_pics/" . $new_filename;

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    $profile_pic_sql = ", profile_pic = '$new_filename'";
                    $_SESSION['profile_pic'] = $new_filename;
                }
            }
        }

        $sql = "UPDATE users SET fullname='$fullname', phone='$phone', gender='$gender', dob='$dob', address='$address' $profile_pic_sql WHERE id=$user_id";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['fullname'] = $fullname;
            $message = "<div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i class='fas fa-check-circle'></i> Profile synchronization complete.</div>";
        }
    }
}

$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
include 'includes/header.php'; 
?>

<div class="container fade-in" style="padding-top: 5rem; padding-bottom: 5rem; max-width: 800px;">
    <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Modify Identity</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Update your clinical record and communication preferences.</p>
        </div>
        <a href="profile.php" class="btn btn-secondary" style="padding: 0.8rem 1.5rem;"><i class="fas fa-arrow-left"></i> Identity View</a>
    </div>

    <?php echo $message; ?>

    <div class="glass-card">
        <form action="" method="POST" enctype="multipart/form-data">
            <!-- Visual Identity Segment -->
            <div style="text-align: center; margin-bottom: 4rem; padding-bottom: 3rem; border-bottom: 1px solid var(--border-glass);">
                <div style="position: relative; width: 150px; height: 150px; margin: 0 auto 2rem;">
                    <div id="imagePreviewContainer" style="width: 150px; height: 150px; border-radius: 50%; border: 4px solid var(--primary); overflow: hidden; background: var(--bg-card); box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                        <?php if (!empty($user['profile_pic']) && $user['profile_pic'] != 'default_user.png'): ?>
                            <img id="currentAvatar" src="assets/uploads/profile_pics/<?php echo $user['profile_pic']; ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=random&color=fff&size=200'">
                        <?php else: ?>
                            <img id="currentAvatar" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=random&color=fff&size=200" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                    <label for="profile_pic" class="upload-badge" style="position: absolute; bottom: 5px; right: 5px; width: 45px; height: 45px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 4px solid var(--bg-card); transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" name="profile_pic" id="profile_pic" style="display: none;" accept="image/*">
                </div>
                <h3 style="color: var(--text-main);">Personal Avatar</h3>
                <p style="font-size: 0.85rem; color: var(--text-dim);">Supported formats: JPG, PNG, GIF (Max 2MB)</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname']; ?>" placeholder="Enter authorized name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Node <i class="fas fa-lock" style="font-size: 0.7rem; opacity: 0.5;"></i></label>
                    <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                    <small style="color: var(--text-dim);">System locked for security.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority Contact</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" placeholder="10-digit mobile" maxlength="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Temporal Logic (DOB)</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo $user['dob']; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Identity Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select Identity</option>
                        <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">Residential Address Ledger</label>
                <textarea name="address" class="form-control" rows="4" placeholder="Full residential data..."><?php echo $user['address']; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-upload-submit" style="width: 100%; padding: 1.2rem; margin-top: 2rem; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 12px; transition: 0.3s;">
                <span>Save All Changes</span>
                <i class="fas fa-cloud-arrow-up"></i>
            </button>
        </form>
    </div>
</div>

<style>
.upload-badge:hover {
    transform: scale(1.15) rotate(15deg);
    background: var(--secondary) !important;
}
.btn-upload-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
}
.image-preview-anim {
    animation: previewBounce 0.5s ease;
}
@keyframes previewBounce {
    0% { transform: scale(0.8); opacity: 0; }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
document.getElementById('profile_pic').onchange = function(evt) {
    const [file] = evt.target.files;
    if (file) {
        const preview = document.getElementById('currentAvatar');
        const container = document.getElementById('imagePreviewContainer');
        
        // Show local preview
        preview.src = URL.createObjectURL(file);
        
        // Add animation
        container.classList.remove('image-preview-anim');
        void container.offsetWidth; // Trigger reflow
        container.classList.add('image-preview-anim');
        
        // Optional: Show a small toast or label that image is ready to upload
    }
};
</script>

<?php include 'includes/footer.php'; ?>