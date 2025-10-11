<?php
require_once '../auth_middleware.php';
requireRole(['staff']);
require_once '../rdb.php';

$profileMsg = '';
$passwordMsg = '';

// current user id from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ../login.php');
    exit();
}

$conn = connect_db();

// load current user and staff rows
$userStmt = $conn->prepare('SELECT id, name, email, photo FROM users WHERE id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userRes = $userStmt->get_result();
$user = $userRes ? $userRes->fetch_assoc() : null;
$userStmt->close();

$staff = null;
$staffStmt = $conn->prepare('SELECT id, dutyFrom, dutyTo FROM staff WHERE userId = ? LIMIT 1');
if ($staffStmt) {
    $staffStmt->bind_param('i', $userId);
    $staffStmt->execute();
    $sres = $staffStmt->get_result();
    $staff = $sres ? $sres->fetch_assoc() : null;
    $staffStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile (name, email, phone/duty times)
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dutyFrom = trim($_POST['dutyFrom'] ?? '');
        $dutyTo = trim($_POST['dutyTo'] ?? '');

        if ($name === '' || $email === '') {
            $profileMsg = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileMsg = 'Please enter a valid email address.';
        } else {
            // update users table
            $uStmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            if ($uStmt) {
                $uStmt->bind_param('ssi', $name, $email, $userId);
                if ($uStmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $profileMsg = 'Profile updated successfully.';
                } else {
                    $profileMsg = 'Failed to update user: ' . $uStmt->error;
                }
                $uStmt->close();
            } else {
                $profileMsg = 'Failed to prepare user update: ' . $conn->error;
            }

            // update staff duty times (insert or update)
            if ($dutyFrom !== '' || $dutyTo !== '') {
                if ($staff) {
                    $sUpd = $conn->prepare('UPDATE staff SET dutyFrom = ?, dutyTo = ? WHERE id = ?');
                    if ($sUpd) {
                        $sid = (int)$staff['id'];
                        $sUpd->bind_param('ssi', $dutyFrom, $dutyTo, $sid);
                        if (!$sUpd->execute()) {
                            $profileMsg .= ' Failed to update staff duty times: ' . $sUpd->error;
                        }
                        $sUpd->close();
                    }
                } else {
                    $sIns = $conn->prepare('INSERT INTO staff (userId, dutyFrom, dutyTo) VALUES (?, ?, ?)');
                    if ($sIns) {
                        $sIns->bind_param('iss', $userId, $dutyFrom, $dutyTo);
                        if ($sIns->execute()) {
                            // refresh staff
                            $newId = $sIns->insert_id;
                            $staff = ['id' => $newId, 'dutyFrom' => $dutyFrom, 'dutyTo' => $dutyTo];
                        } else {
                            $profileMsg .= ' Failed to create staff record: ' . $sIns->error;
                        }
                        $sIns->close();
                    }
                }
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
            // verify current password (stored as plaintext in this demo app)
            $pwStmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
            if ($pwStmt) {
                $pwStmt->bind_param('i', $userId);
                $pwStmt->execute();
                $gres = $pwStmt->get_result();
                $row = $gres ? $row = $gres->fetch_assoc() : null;
                $pwStmt->close();
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
                    }
                }
            } else {
                $passwordMsg = 'Failed to prepare password check: ' . $conn->error;
            }
        }
    }

    // refresh user data after POST
    $userStmt = $conn->prepare('SELECT id, name, email, photo FROM users WHERE id = ? LIMIT 1');
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $userRes = $userStmt->get_result();
    $user = $userRes ? $userRes->fetch_assoc() : $user;
    $userStmt->close();

    $staffStmt = $conn->prepare('SELECT id, dutyFrom, dutyTo FROM staff WHERE userId = ? LIMIT 1');
    if ($staffStmt) {
        $staffStmt->bind_param('i', $userId);
        $staffStmt->execute();
        $sres = $staffStmt->get_result();
        $staff = $sres ? $sres->fetch_assoc() : $staff;
        $staffStmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile</title>
    <link rel="stylesheet" href="../../css/staff/staff-profile.css">
    <link rel="stylesheet" href="../../css/staff/staff-common.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="staff-orders.php">Order</a></li>
                <li><a href="staff-active-orders.php">Active Orders</a></li>
                <li><a href="staff-profile.php">Profile</a></li>
                <li><a href="../logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
            </ul>
        </nav>
        <div class="profile-container">
            <div class="profile-header">
                    <img src="<?php echo htmlspecialchars($user['photo'] ? '../../resources/' . $user['photo'] : '../../resources/staff.png', ENT_QUOTES); ?>" alt="Profile Picture" class="profile-picture">
                    <div class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'Staff', ENT_QUOTES); ?></div>
                </div>
                <h2>Staff Profile</h2>
                <form class="profile-form" method="post" action="" novalidate>
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="dutyFrom">Duty From:</label>
                        <input type="time" id="dutyFrom" name="dutyFrom" value="<?php echo htmlspecialchars($staff['dutyFrom'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="dutyTo">Duty To:</label>
                        <input type="time" id="dutyTo" name="dutyTo" value="<?php echo htmlspecialchars($staff['dutyTo'] ?? '', ENT_QUOTES); ?>">
                    </div>
                            <div class="form-group">
                                <button type="button" class="change-password-btn" id="openPasswordModal">Change Password</button>
                            </div>
                        <!-- Password Change Modal (moved outside main form to avoid nested forms) -->
                <input type="hidden" name="update_profile" value="1" />
                <button type="submit" class="update-profile-btn">Update Profile</button>
                <?php if ($profileMsg): ?>
                    <p class="error"><?php echo htmlspecialchars($profileMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
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
