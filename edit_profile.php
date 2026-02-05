<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $address = $conn->real_escape_string($_POST['address']);

    // Server-side Validation
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $message = "<div class='alert-error'>Invalid phone number. Please enter a valid 10-digit mobile number.</div>";
    } else {

        // Handle File Upload
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
                    $_SESSION['profile_pic'] = $new_filename; // Update session
                } else {
                    $message = "<div class='alert-error'>Failed to upload image.</div>";
                }
            } else {
                $message = "<div class='alert-error'>Invalid file type. Only JPG, PNG, GIF allowed.</div>";
            }
        }

        $sql = "UPDATE users SET fullname='$fullname', phone='$phone', gender='$gender', dob='$dob', address='$address' $profile_pic_sql WHERE id=$user_id";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['fullname'] = $fullname; // Update session
            $message = "<div class='alert-success'>Profile updated successfully!</div>";
        } else {
            $message = "<div class='alert-error'>Error updating profile: " . $conn->error . "</div>";
        }
    } // End of else block for validation
}

// Fetch Current Data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>

<div class="container section">
    <div style="max-width: 800px; margin: 0 auto;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Edit Profile</h2>
            <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Profile</a>
        </div>

        <?php echo $message; ?>

        <div class="card">
            <form action="" method="POST" enctype="multipart/form-data">

                <div style="text-align: center; margin-bottom: 30px;">
                    <div
                        style="width: 120px; height: 120px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; overflow: hidden; border: 4px solid white; box-shadow: var(--shadow-md); position: relative;">
                        <?php if (!empty($user['profile_pic']) && $user['profile_pic'] != 'default_user.png'): ?>
                            <img src="assets/uploads/profile_pics/<?php echo $user['profile_pic']; ?>" alt="Profile"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user" style="font-size: 3rem; color: #ccc;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="profile_pic" class="btn btn-outline" style="cursor: pointer;">
                            <i class="fas fa-camera"></i> Change Photo
                        </label>
                        <input type="file" name="profile_pic" id="profile_pic" style="display: none;"
                            onchange="document.getElementById('file-name').innerText = this.files[0].name">
                        <p id="file-name" style="margin-top: 5px; font-size: 0.8rem; color: var(--text-muted);"></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname']; ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Email (Cannot change)</label>
                        <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled
                            pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Must be a valid Gmail address">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>"
                            placeholder="Enter 10-digit mobile number" pattern="[6-9][0-9]{9}" maxlength="10"
                            title="Please enter a valid 10-digit mobile number starting with 6-9" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?php echo $user['dob']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php if ($user['gender'] == 'Male')
                                echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if ($user['gender'] == 'Female')
                                echo 'selected'; ?>>Female
                            </option>
                            <option value="Other" <?php if ($user['gender'] == 'Other')
                                echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo $user['address']; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 20px;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>