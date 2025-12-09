<?php
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*, r.display_name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = '$user_id'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

$success_messages = [
    'profile_updated' => 'Profile updated successfully!',
    'password_changed' => 'Password changed successfully!'
];

$error_messages = [
    'all_fields_required' => 'All fields are required.',
    'passwords_dont_match' => 'New passwords do not match.',
    'password_too_short' => 'Password must be at least 6 characters long.',
    'current_password_incorrect' => 'Current password is incorrect.',
    'user_not_found' => 'User not found.',
    'update_failed' => 'Failed to update. Please try again.',
    'invalid_email' => 'Please enter a valid email address.',
    'email_exists' => 'Email is already taken by another user.'
];
?>

<div class="profile-container">
    <?php if ($success && isset($success_messages[$success])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_messages[$success]; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error && isset($error_messages[$error])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_messages[$error]; ?>
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        
        <div class="profile-card">
            <div class="profile-card-header">
                <h3><i class="fas fa-user"></i> Profile Information</h3>
            </div>
            <div class="profile-card-body">
                <form action="<?php echo BASE_URL; ?>/api/profile.php" method="POST" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="profile-avatar-section">
                        <div class="profile-avatar-large">
                            <?php echo strtoupper(substr($user_data['full_name'], 0, 2)); ?>
                        </div>
                        <div class="profile-info">
                            <h4><?php echo htmlspecialchars($user_data['full_name']); ?></h4>
                            <p class="text-muted">@<?php echo htmlspecialchars($user_data['username']); ?></p>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($user_data['role_name'] ?? 'User'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                        <small class="form-text">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Account Status</label>
                        <div class="status-badge <?php echo $user_data['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo $user_data['is_active'] ? 'Active' : 'Inactive'; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <div class="profile-card">
            <div class="profile-card-header">
                <h3><i class="fas fa-lock"></i> Change Password</h3>
            </div>
            <div class="profile-card-body">
                <form action="<?php echo BASE_URL; ?>/api/profile.php" method="POST" class="profile-form">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Password must be at least 6 characters long.
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-header">
                <h3><i class="fas fa-info-circle"></i> Account Details</h3>
            </div>
            <div class="profile-card-body">
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-calendar-plus"></i> Created At
                    </div>
                    <div class="detail-value">
                        <?php echo date('M d, Y h:i A', strtotime($user_data['created_at'])); ?>
                    </div>
                </div>
                
                <?php if ($user_data['updated_at']): ?>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-calendar-check"></i> Last Updated
                    </div>
                    <div class="detail-value">
                        <?php echo date('M d, Y h:i A', strtotime($user_data['updated_at'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-user-shield"></i> Role
                    </div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($user_data['role_name'] ?? 'User'); ?>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-hashtag"></i> User ID
                    </div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($user_data['id']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease-out';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>
