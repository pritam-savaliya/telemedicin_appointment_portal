<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/header.php';
?>

<div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; background-color: var(--bg-body);">
    <div style="text-align: center; background: var(--bg-white); padding: 3rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); max-width: 500px;">
        <i class="fas fa-pills" style="font-size: 4rem; color: var(--primary-light); margin-bottom: 1.5rem;"></i>
        <h1 style="color: var(--secondary-color); margin-bottom: 1rem;">Order Medicine</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1.1rem;">This feature is currently under development. Stay tuned for updates!</p>
        <a href="home.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>