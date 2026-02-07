<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';

// Create table if not exists
if (
    !$conn->query("CREATE TABLE IF NOT EXISTS doctor_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialty VARCHAR(100),
    qualification VARCHAR(100),
    experience VARCHAR(50),
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)")
) {
    die("Table creation failed: " . $conn->error);
}

// Helper function to upsert profile
function upsertProfile($conn, $user_id, $specialty, $qualification, $experience, $bio)
{
    $check = $conn->query("SELECT id FROM doctor_profiles WHERE user_id=$user_id");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO doctor_profiles (user_id, specialty, qualification, experience, bio) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $specialty, $qualification, $experience, $bio);
        $stmt->execute();
        echo "Inserted profile for User ID $user_id.<br>";
    } else {
        $stmt = $conn->prepare("UPDATE doctor_profiles SET specialty=?, qualification=?, experience=?, bio=? WHERE user_id=?");
        $stmt->bind_param("ssssi", $specialty, $qualification, $experience, $bio, $user_id);
        $stmt->execute();
        echo "Updated profile for User ID $user_id.<br>";
    }
}

// 1. Rename existing generic doctors to real names
// IDs: 2, 4, 5 based on previous check
$conn->query("UPDATE users SET fullname='Dr. Sarah Johnson', gender='Female', profile_pic='assets/images/doc1.jpg' WHERE id=2");
$conn->query("UPDATE users SET fullname='Dr. David Lee', gender='Male', profile_pic='assets/images/doc2.jpg' WHERE id=4");
$conn->query("UPDATE users SET fullname='Dr. James Wilson', gender='Male', profile_pic='assets/images/doc3.jpg' WHERE id=5");

// 2. Create Profiles for existing doctors
$doctors = [
    2 => ['Pediatrician', 'MBBS, MD (Pediatrics)', '8 Years', 'Expert in child healthcare and development.'],
    4 => ['Cardiologist', 'MBBS, MD, DM (Cardiology)', '12 Years', 'Specialist in heart diseases and preventive cardiology.'],
    5 => ['Orthopedic Surgeon', 'MBBS, MS (Orthopedics)', '10 Years', 'Focused on bone health, fractures, and joint replacements.']
];

foreach ($doctors as $id => $details) {
    upsertProfile($conn, $id, $details[0], $details[1], $details[2], $details[3]);
}

// 3. Add 3 New Doctors (including Antigravity)
$new_doctors = [
    [
        'name' => 'Dr. Antigravity AI',
        'email' => 'ai@telemed.com',
        'gender' => 'Other',
        'specialty' => 'Artificial Intelligence & Diagnosis',
        'qualification' => 'PhD in AI Medicine',
        'experience' => 'Infinite',
        'bio' => 'Advanced AI doctor capable of diagnosing complex conditions with high precision.'
    ],
    [
        'name' => 'Dr. Emily Stone',
        'email' => 'emily@telemed.com',
        'gender' => 'Female',
        'specialty' => 'Dermatologist',
        'qualification' => 'MBBS, MD (Dermatology)',
        'experience' => '6 Years',
        'bio' => 'Specializes in skin care, cosmetic procedures, and treating skin diseases.'
    ],
    [
        'name' => 'Dr. Michael Chen',
        'email' => 'michael@telemed.com',
        'gender' => 'Male',
        'specialty' => 'Neurologist',
        'qualification' => 'MBBS, MD, DM (Neurology)',
        'experience' => '15 Years',
        'bio' => 'Expert in disorders of the nervous system, brain, and spinal cord.'
    ]
];

foreach ($new_doctors as $doc) {
    // Check if email exists
    $email = $doc['email'];
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, is_approved, gender, profile_pic) VALUES (?, ?, ?, 'doctor', 1, ?, 'assets/images/default_user.png')");
        $pass = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->bind_param("ssss", $doc['name'], $email, $pass, $doc['gender']);
        $stmt->execute();
        $user_id = $conn->insert_id;
        echo "Created User " . $doc['name'] . "<br>";
    } else {
        $user_id = $check->fetch_assoc()['id'];
        echo "User " . $doc['name'] . " exists (ID: $user_id).<br>";
    }

    // Always upsert profile
    upsertProfile($conn, $user_id, $doc['specialty'], $doc['qualification'], $doc['experience'], $doc['bio']);
}

echo "Doctors setup completed.";
?>