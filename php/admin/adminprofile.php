<?php
require_once 'admin_functions.php';
validateAdminAccess();
require_once __DIR__ . '/../rdb.php';

$profileMsg = '';
$passwordMsg = '';
$uploadErr = '';

// current user id from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ../login.php');
    exit();
}

$conn = connect_db();

// helper: fetch current user
function fetch_user($conn, $userId) {
    $stmt = $conn->prepare('SELECT id, name, email, photo, password FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

$user = fetch_user($conn, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile (name, email, optional photo)
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $profileMsg = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileMsg = 'Please enter a valid email address.';
        } else {
            // handle optional photo upload
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['photo'];
                $allowed = ['jpg','jpeg','png','gif'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) {
                    $uploadErr = 'Unsupported image format.';
                } else {
                    $uploadDir = __DIR__ . '/../../resources/uploads/users/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $newName = uniqid('u_', true) . '.' . $ext;
                    $dest = $uploadDir . $newName;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $photoPath = 'uploads/users/' . $newName; // stored relative to resources/
                    } else {
                        $uploadErr = 'Failed to move uploaded file.';
                    }
                }
            }

            // update DB
            $fields = 'name = ?, email = ?';
            $params = [$name, $email];
            $types = 'ss';
            if (!empty($photoPath)) {
                $fields .= ', photo = ?';
                $types .= 's';
                $params[] = $photoPath;
            }
            $types .= 'i';
            $params[] = $userId;

            $sql = "UPDATE users SET {$fields} WHERE id = ?";
            $uStmt = $conn->prepare($sql);
            if ($uStmt) {
                $uStmt->bind_param($types, ...$params);
                if ($uStmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $profileMsg = 'Profile updated successfully.' . ($uploadErr ? ' Photo: ' . $uploadErr : '');
                } else {
                    $profileMsg = 'Failed to update user: ' . $uStmt->error;
                }
                $uStmt->close();
            } else {
                $profileMsg = 'Failed to prepare user update: ' . $conn->error;
            }
        }
    }

    // Password change
    if (isset($_POST['change_password'])) {
        $current = $_POST['current-password'] ?? '';
        $new = $_POST['new-password'] ?? '';
        $confirm = $_POST['confirm-password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $passwordMsg = 'All password fields are required.';
        } elseif ($new !== $confirm) {
            $passwordMsg = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $passwordMsg = 'New password must be at least 6 characters.';
        } else {
            // verify stored password (preserve existing plaintext logic)
            $row = fetch_user($conn, $userId);
            $stored = $row['password'] ?? null;
            if ($stored === null) {
                $passwordMsg = 'Unable to verify current password.';
            } elseif (!hash_equals((string)$stored, (string)$current)) {
                $passwordMsg = 'Current password is incorrect.';
            } else {
                $upd = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                if ($upd) {
                    $upd->bind_param('si', $new, $userId);
                    if ($upd->execute()) {
                        $passwordMsg = 'Password changed successfully.';
                    } else {
                        $passwordMsg = 'Failed to update password: ' . $upd->error;
                    }
                    $upd->close();
                } else {
                    $passwordMsg = 'Failed to prepare password update: ' . $conn->error;
                }
            }
        }
    }

    // refresh user data after POST
    $user = fetch_user($conn, $userId) ?: $user;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../../css/staff/staff-profile.css">
    <link rel="stylesheet" href="../../css/staff/staff-common.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="manage-products.php">Products</a></li>
                <li><a href="manage-users.php">Users</a></li>
                <li><a href="adminprofile.php" class="active">Profile</a></li>
                <li><a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
            </ul>
        </nav>

        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($user['photo'] ? '../../resources/' . $user['photo'] : '../../resources/userphoto.jpg', ENT_QUOTES); ?>" alt="Profile Picture" class="profile-picture">
                <div class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'Admin', ENT_QUOTES); ?></div>
            </div>

            <h2>Admin Profile</h2>
            <form class="profile-form" method="post" action="" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES); ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'admin', ENT_QUOTES); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="photo">Update Picture:</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="button" class="change-password-btn" id="openPasswordModal">Change Password</button>
                </div>

                <input type="hidden" name="update_profile" value="1" />
                <button type="submit" class="update-profile-btn">Update Profile</button>

                <?php if ($profileMsg): ?>
                    <p class="error"><?php echo htmlspecialchars($profileMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($uploadErr): ?>
                    <p class="error"><?php echo htmlspecialchars($uploadErr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($passwordMsg): ?>
                    <p class="error"><?php echo htmlspecialchars($passwordMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                <?php endif; ?>
            </form>

            <!-- Password Change Modal (separate form) -->
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
        // Modal open/close logic (same pattern as staff profile)
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