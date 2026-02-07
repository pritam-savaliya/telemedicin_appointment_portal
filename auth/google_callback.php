<?php
include '../includes/db.php';
require_once '../includes/google_config.php';
session_start();

if (isset($_GET['code'])) {
    $token_url = 'https://oauth2.googleapis.com/token';
    $curl_data = [
        'code' => $_GET['code'],
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curl_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_data['access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $user_info = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_info, true);

        $google_id = $user_data['id'];
        $email = $user_data['email'];
        $name = $user_data['name'];

        // 1. Check if user exists by google_id
        $sql = "SELECT * FROM users WHERE google_id = '$google_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // User exists, log them in
            $row = $result->fetch_assoc();
            loginUser($row);
        } else {
            // 2. Check if user exists by email
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // User exists, update google_id
                $row = $result->fetch_assoc();
                $user_id = $row['id'];
                $update_sql = "UPDATE users SET google_id = '$google_id', email_verified = 1 WHERE id = '$user_id'";
                $conn->query($update_sql);
                loginUser($row);
            } else {
                // 3. Create new user
                $password = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
                $role = 'patient'; // Default role
                $is_approved = 1; // Auto-approve Google users

                $insert_sql = "INSERT INTO users (fullname, email, password, role, is_approved, google_id, email_verified) VALUES ('$name', '$email', '$password', '$role', $is_approved, '$google_id', 1)";

                if ($conn->query($insert_sql) === TRUE) {
                    $new_user_id = $conn->insert_id;
                    $new_user_sql = "SELECT * FROM users WHERE id = '$new_user_id'";
                    $new_user_result = $conn->query($new_user_sql);
                    $new_user_row = $new_user_result->fetch_assoc();
                    loginUser($new_user_row);
                } else {
                    echo "Error: " . $conn->error;
                }
            }
        }
    } else {
        echo "Error fetching access token";
    }
} else {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

function loginUser($row)
{
    if ($row['is_approved'] == 0) {
        header("Location: " . BASE_URL . "auth/login.php?success=2"); // Account pending approval
        exit();
    }

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['fullname'] = $row['fullname'];
    $_SESSION['role'] = $row['role'];

    if ($row['role'] == 'admin') {
        header("Location: " . BASE_URL . "admin/admin_dashboard.php?msg=login_success");
    } elseif ($row['role'] == 'patient') {
        header("Location: " . BASE_URL . "patient/patient_dashboard.php?msg=login_success");
    } else {
        header("Location: " . BASE_URL . "doctor/doctor_dashboard.php?msg=login_success");
    }
    exit();
}
?>