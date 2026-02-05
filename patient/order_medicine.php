<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
?>

<div class="container section">
    <div style="margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color);">Order Medicine</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Upload a prescription or describe your needs, and we'll
            deliver it to your door.</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="toast-notification show" style="background-color: var(--success-color);">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="toast-notification show" style="background-color: var(--danger-color);">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">

        <!-- Order Form -->
        <div class="card">
            <h3 style="margin-bottom: 20px; color: var(--primary-color);"><i class="fas fa-cart-plus"></i> Place New
                Order</h3>
            <form action="<?php echo BASE_URL; ?>patient/place_order.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Delivery Address <span style="color: red;">*</span></label>
                    <textarea name="delivery_address" class="form-control" rows="3" required
                        placeholder="Enter your full delivery address..."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Prescription (Image/PDF)</label>
                    <input type="file" name="prescription" class="form-control" accept="image/*,.pdf">
                    <small style="color: var(--text-muted);">Recommended for accuracy.</small>
                </div>

                <div style="text-align: center; margin: 15px 0; color: var(--text-muted); font-weight: 600;">- OR -
                </div>

                <div class="form-group">
                    <label>Manual Order Details</label>
                    <textarea name="order_details" class="form-control" rows="4"
                        placeholder="List medicines (e.g., Paracetamol 500mg - 1 strip)..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i> Place Order
                </button>
            </form>
        </div>

        <!-- Order History -->
        <div class="card"
            style="padding: 0; overflow: hidden; max-height: 600px; display: flex; flex-direction: column;">
            <div style="padding: 20px; border-bottom: 1px solid #eee; background: #fafafa;">
                <h3 style="margin: 0; color: var(--secondary-color);"><i class="fas fa-history"></i> Your Order History
                </h3>
            </div>

            <div class="table-container" style="box-shadow: none; border-radius: 0; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders_sql = "SELECT * FROM medicine_orders WHERE patient_id = $user_id ORDER BY created_at DESC";
                        $orders_result = $conn->query($orders_sql);

                        if ($orders_result->num_rows > 0):
                            while ($order = $orders_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $status = $order['status'];
                                        $badge = 'badge-pending';
                                        if ($status == 'delivered')
                                            $badge = 'badge-success';
                                        elseif ($status == 'cancelled')
                                            $badge = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($order['prescription_path']): ?>
                                            <a href="<?php echo $order['prescription_path']; ?>" target="_blank"
                                                style="color: var(--primary-color);">
                                                <i class="fas fa-file-prescription"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span title="<?php echo htmlspecialchars($order['order_details']); ?>"
                                                style="cursor: help; border-bottom: 1px dotted #ccc;">
                                                Text
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-muted);">No
                                    orders placed yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple script to auto-hide toasts after 5 seconds
    setTimeout(() => {
        const toasts = document.querySelectorAll('.toast-notification');
        toasts.forEach(t => {
            t.classList.remove('show');
            setTimeout(() => t.remove(), 500); // Remove from DOM after transition
        });
    }, 5000);
</script>

<?php include '../includes/footer.php'; ?>
