# Deployment Guide for TeleMedicine Application

This guide will walk you through deploying your TeleMedicine Application from your local XAMPP setup to a live web hosting server (e.g., Hostinger, Bluehost, GoDaddy, InfinityFree).

## Prerequisites
- A domain name (e.g., `yourdomain.com`).
- A web hosting account with:
  - **PHP 7.4 or higher**.
  - **MySQL Database**.
  - **phpMyAdmin** access.
  - **File Manager** or **FTP** access.

---

## Step 1: Export Your Database
1. Open **phpMyAdmin** on your local machine (`http://localhost/phpmyadmin`).
2. Select your database: `telemedicine_db`.
3. Click on the **Export** tab.
4. Keep the format as **SQL** and click **Export**.
5. Save the `.sql` file to your computer (e.g., `telemedicine_db.sql`).

---

## Step 2: Prepare Project Files
1. Navigate to your project folder: `c:\xampp\htdocs\telemedicine_appointment`.
2. Select all files and folders inside this directory.
3. Right-click and compress them into a `.zip` file (e.g., `project_files.zip`).
   - **Note:** Do not zip the parent folder, zip the *contents*.

---

## Step 3: Upload Files to Hosting
1. Log in to your hosting account's Control Panel (cPanel).
2. Open **File Manager**.
3. Navigate to the `public_html` directory (or the specific folder for your domain).
4. Click **Upload** and select your `project_files.zip`.
5. Once uploaded, right-click the zip file and choose **Extract**.
6. Ensure all files are in the correct directory. You can delete the zip file afterward.

---

## Step 4: Create Live Database
1. In cPanel, find **MySQL Database Wizard** or **MySQL Databases**.
2. Create a new database (e.g., `u12345_telemedicine`).
3. Create a new database user (e.g., `u12345_admin`) and set a strong password.
4. **Important:** Add the user to the database and grant **All Privileges**.
5. Note down the **Database Name**, **Username**, and **Password**.

---

## Step 5: Import Database
1. Go back to cPanel main page and open **phpMyAdmin**.
2. Select your newly created database from the left sidebar.
3. Click on the **Import** tab.
4. Choose the `.sql` file you exported in Step 1.
5. Click **Import** or **Go**.

---

## Step 6: Update Configuration Files

You must update your code to connect to the new live database.

### 1. Update Database Connection
Edit `includes/db.php` in your File Manager:

```php
<?php
$servername = "localhost"; // Usually 'localhost', check your host details
$username = "u12345_admin"; // YOUR LIVE DATABASE USERNAME
$password = "StrongPassword123!"; // YOUR LIVE DATABASE PASSWORD
$dbname = "u12345_telemedicine"; // YOUR LIVE DATABASE NAME

// Connect...
$conn = new mysqli($servername, $username, $password, $dbname);

// ...

// IMPORTANT: Update standard Base URL to your domain
define('BASE_URL', 'https://yourdomain.com/'); 
?>
```

### 2. Update Google Authentication (If used)
Edit `includes/google_config.php`:
- Change `GOOGLE_REDIRECT_URL` to `https://yourdomain.com/auth/google_callback.php`.
- Go to [Google Cloud Console](https://console.cloud.google.com/).
- Navigate to **APIs & Services > Credentials**.
- Edit your OAuth 2.0 Client ID.
- Add your new domain to **Authorized JavaScript origins** (e.g., `https://yourdomain.com`).
- Add the new callback URL to **Authorized redirect URIs** (e.g., `https://yourdomain.com/auth/google_callback.php`).

### 3. Update Email Settings (Optional)
Edit `includes/smtp_config.php` with valid SMTP credentials if you want email notifications to work reliably on the live server.

---

## Step 7: Final Testing
1. Visit your domain `https://yourdomain.com`.
2. Try logging in.
3. Test a registration.
4. If you see errors, check the error logs in your hosting control panel.

**Common Issues:**
- **White Screen / 500 Error:** Check file permissions (Folders: 755, Files: 644) or PHP version compatibility.
- **Database Connection Error:** Verify credentials in `includes/db.php`.
- **404 Not Found:** Ensure `.htaccess` is configured if you use RewriteRules (this project currently uses standard standard `.php` links so this shouldn't be an issue unless you add routing).
