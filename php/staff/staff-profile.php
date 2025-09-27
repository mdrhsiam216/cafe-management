<?php
// Basic server-side validation for profile update and password change (demo)
session_start();

$profileMsg = '';
$passwordMsg = '';

// Simulate current password for demo purposes
$demoCurrentPassword = 'password123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

        if ($name === '' || $email === '') {
            $profileMsg = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileMsg = 'Please enter a valid email address.';
        } else {
            $profileMsg = 'Profile updated successfully (demo).';
        }
    }

    if (isset($_POST['change_password'])) {
        $current = isset($_POST['current-password']) ? $_POST['current-password'] : '';
        $new = isset($_POST['new-password']) ? $_POST['new-password'] : '';
        $confirm = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : '';

        if ($current === '' || $new === '' || $confirm === '') {
            $passwordMsg = 'All password fields are required.';
        } elseif ($current !== $demoCurrentPassword) {
            $passwordMsg = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $passwordMsg = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $passwordMsg = 'New passwords do not match.';
        } else {
            $passwordMsg = 'Password changed successfully (demo).';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile</title>
    <link rel="stylesheet" href="../CSS/staff-profile.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="staff-orders.php">Order</a></li>
                <li><a href="staff-payments.php">Payments</a></li>
                <li><a href="staff-active-orders.php">Active Orders</a></li>
                <li><a href="#about-section">About</a></li>
                <li><a href="#contact-section">Contact</a></li>
                <li><a href="staff-profile.php">Profile</a></li>
                <li>
                    <a href="staff-login.php" class="logout-icon" title="Log out" aria-label="Log out">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13 19H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="profile-container">
            <div class="profile-header">
                <img src="../../resources/staff.png" alt="Profile Picture" class="profile-picture">
                <div class="user-name">John Doe</div>
            </div>
            <h2>Staff Profile</h2>
            <form class="profile-form" method="post" action="" novalidate>
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" value="John Doe" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="john.doe@example.com" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" value="123-456-7890">
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="1990-01-01">
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="male" selected>Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <input type="text" id="role" name="role" value="Barista" readonly>
                </div>
                        <div class="form-group">
                            <button type="button" class="change-password-btn" id="openPasswordModal">Change Password</button>
                        </div>
                        <!-- Password Change Modal -->
                        <div id="passwordModal" class="modal">
                            <div class="modal-content">
                                <span class="close" id="closePasswordModal">&times;</span>
                                <h3>Change Password</h3>
                                <form class="password-form" method="post" action="" novalidate>
                                    <div class="form-group">
                                        <label for="current-password">Current Password:</label>
                                        <input type="password" id="current-password" name="current-password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new-password">New Password:</label>
                                        <input type="password" id="new-password" name="new-password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm-password">New Password Again:</label>
                                        <input type="password" id="confirm-password" name="confirm-password" required>
                                    </div>
                                    <input type="hidden" name="change_password" value="1" />
                                    <button type="submit" class="confirm-btn">Confirm</button>
                                </form>
                            </div>
                        </div>
                <input type="hidden" name="update_profile" value="1" />
                <button type="submit" class="update-profile-btn">Update Profile</button>
                <?php if ($profileMsg): ?>
                    <p class="error"><?php echo htmlspecialchars($profileMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($passwordMsg): ?>
                    <p class="error"><?php echo htmlspecialchars($passwordMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                <?php endif; ?>
            </form>
        </div>
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section" id="contact-section">
                    <h3>Contact Us</h3>
                    <p>
                        Email:
                        <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a>
                    </p>
                    <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                    <p>Address: 123 Skyline Avenue, Dhaka</p>
                </div>
                <div class="footer-section" id="about-section">
                    <h3>About Us</h3>
                    <p>
                        We are passionate about serving the finest coffee, crafted with
                        love and expertise. Join us for a unique coffee experience!
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Subscribe for exclusive offers!</p>
                    <input type="email" placeholder="Enter your email" class="newsletter-input" />
                    <button class="btn newsletter-btn">Subscribe</button>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="https://facebook.com" class="social-icon" aria-label="Facebook">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo" class="social-logo" />
                        </a>
                        <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo" class="social-logo" />
                        </a>
                        <a href="https://x.com" class="social-icon" aria-label="X">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo" />
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
        <script>
            // Modal open/close logic
            const modal = document.getElementById('passwordModal');
            const openBtn = document.getElementById('openPasswordModal');
            const closeBtn = document.getElementById('closePasswordModal');
            openBtn.onclick = function() {
                modal.style.display = 'block';
            }
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        </script>
</body>
</html>
