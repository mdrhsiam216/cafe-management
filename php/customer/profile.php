<?php
session_start();
require_once '../rdb.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($name) || empty($email)) {
        $error_message = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $conn = connect_db();
        
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->fetch_assoc()) {
            $error_message = 'Email already exists for another account.';
        } else {
            // If password change is requested
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error_message = 'All password fields are required to change password.';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error_message = 'New password must be at least 6 characters long.';
                } else {
                    // Verify current password
                    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user_data = $result->fetch_assoc();
                    
                    if ($current_password !== $user_data['password']) {
                        $error_message = 'Current password is incorrect.';
                    } else {
                        // Update with new password
                        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $name, $email, $new_password, $user_id);
                        if ($stmt->execute()) {
                            $success_message = 'Profile and password updated successfully!';
                        } else {
                            $error_message = 'Error updating profile: ' . $conn->error;
                        }
                    }
                }
            } else {
                // Update without password change
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $user_id);
                if ($stmt->execute()) {
                    $success_message = 'Profile updated successfully!';
                } else {
                    $error_message = 'Error updating profile: ' . $conn->error;
                }
            }
        }
        $stmt->close();
        $conn->close();
    }
}

// Fetch current user data
$conn = connect_db();
$stmt = $conn->prepare("SELECT name, email, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: ../login.php');
    exit();
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="profile-box">
            <div class="hero-section">
                <img src="../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>My Profile</h1>
                <p>Manage your account information and preferences</p>
            </div>

            <section class="profile-section">
                <?php if ($success_message): ?>
                    <div class="message success-message">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="message error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form id="profile-form" method="POST">
                    <div class="form-section">
                        <h2>Personal Information</h2>
                        
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Change Password</h2>
                        <p class="section-note">Leave blank if you don't want to change your password</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" minlength="6">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="../menu.php" class="btn btn-secondary">Back to Menu</a>
                    </div>
                </form>
            </section>

            <footer class="footer">
                <div class="footer-content">
                    <div class="footer-section" id="contact-section">
                        <h3>Contact Us</h3>
                        <p>Email: <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a></p>
                        <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                        <p>Address: 123 Skyline Avenue, Dhaka</p>
                    </div>
                    <div class="footer-section" id="about-section">
                        <h3>About Us</h3>
                        <p>We are passionate about serving the finest coffee, crafted with love and expertise. Join us for a unique coffee experience!</p>
                    </div>
                    <div class="footer-section">
                        <h3>Newsletter</h3>
                        <p>Subscribe for exclusive offers!</p>
                        <input type="email" placeholder="Enter your email" class="newsletter-input">
                        <button class="btn newsletter-btn">Subscribe</button>
                    </div>
                    <div class="footer-section">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="https://facebook.com" class="social-icon" aria-label="Facebook">
                                <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo" class="social-logo">
                            </a>
                            <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                                <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo" class="social-logo">
                            </a>
                            <a href="https://x.com" class="social-icon" aria-label="X">
                                <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>Skyline Coffee Shop - Where Every Sip Tells a Story</p>
                    <p>&copy; 2025 Skyline Coffee Shop. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;

            // If any password field is filled, all must be filled
            if (newPassword || confirmPassword || currentPassword) {
                if (!currentPassword) {
                    alert('Please enter your current password to change it.');
                    e.preventDefault();
                    return;
                }
                if (!newPassword) {
                    alert('Please enter a new password.');
                    e.preventDefault();
                    return;
                }
                if (!confirmPassword) {
                    alert('Please confirm your new password.');
                    e.preventDefault();
                    return;
                }
                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match.');
                    e.preventDefault();
                    return;
                }
                if (newPassword.length < 6) {
                    alert('New password must be at least 6 characters long.');
                    e.preventDefault();
                    return;
                }
            }
        });

        // Auto-hide success/error messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>
