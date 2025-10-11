<?php
// Simple server-side validation and demo authentication for staff login
session_start();
require_once '../rdb.php';

$errorMessage = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic sanitization
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']);

    $emailValue = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif ($password === '' || strlen($password) < 1) {
        $errorMessage = 'Password is required.';
    } else {
        // DB lookup for staff user
        $conn = connect_db();
        $stmt = $conn->prepare('SELECT id, name, email, role, password FROM users WHERE email = ? AND role = "staff" LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                $user = $res->fetch_assoc();
                $stored = $user['password'];
                // plaintext comparison (project uses plaintext passwords)
                if (hash_equals((string)$stored, (string)$password)) {
                    // set session similar to global login
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($remember) {
                        setcookie('staff_remember', $user['email'], time() + (30 * 24 * 60 * 60), '/');
                    }

                    $stmt->close();
                    $conn->close();
                    header('Location: staff-orders.php');
                    exit;
                }
            }
            $stmt->close();
        }
        $conn->close();
        $errorMessage = 'Invalid email or password.';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Staff Login - Skyline Coffee Shop</title>
    <!-- Use the staff orders stylesheet so the login page matches the orders page appearance -->
    <link rel="stylesheet" href="../css/staff/staff-orders.css">
    <link rel="icon" href="../Images/Brown Modern Circle Coffee Shop Logo.png">
</head>

<body>
    <div class="container">
        <div class="orders-box">
            <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo" />
            <h2>Staff Login</h2>

            <form id="staffLoginForm" class="login-form" action="" method="post" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required placeholder="you@domain.com"
                        aria-required="true" value="<?php echo $emailValue; ?>" />
                </div>
                <div class="form-group" style="position:relative;">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="6"
                        placeholder="Enter password" aria-required="true" style="padding-right:40px;" />
                    <button type="button" id="togglePassword" aria-label="Show password" title="Show password"
                        style="position:absolute; right:8px; top:34px; border:none; background:transparent; padding:4px; cursor:pointer;">
                        <svg id="eyeOpen" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" />
                        </svg>
                    </button>
                </div>

                <div class="login-row">
                    <label class="remember"><input type="checkbox" id="remember" name="remember"> Remember me</label>
                    <a class="forgot" href="reset-password.php">Forgot?</a>
                </div>
                <button type="submit" class="btn">Sign in</button>

                <p class="error" aria-live="polite"><?php if ($errorMessage) echo htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                <p class="small">Not staff? <a>Customer login</a></p>
            </form>

            <!-- Reuse the footer from staff-orders.html to keep pages consistent -->
            <footer class="footer">
                <div class="footer-content">
                    <div class="footer-section" id="contact-section">
                        <h3>Contact Us</h3>
                        <p>
                            Email: <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a>
                        </p>
                        <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                        <p>Address: 123 Skyline Avenue, Dhaka</p>
                    </div>
                    <div class="footer-section" id="about-section">
                        <h3>About Us</h3>
                        <p>
                            We are passionate about serving the finest coffee, crafted with love and expertise. Join us
                            for a unique coffee experience!
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
                                <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png"
                                    alt="Facebook Logo" class="social-logo" />
                            </a>

                            <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                                <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png"
                                    alt="Instagram Logo" class="social-logo" />
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
    </div>

    <script>
        // Optional client-side validation can remain but should not block normal form submission.
                (function () {
            const form = document.getElementById('staffLoginForm');
            const errorEl = form.querySelector('.error');
            form.addEventListener('submit', function () {
                errorEl.style.display = 'none';
                const email = document.getElementById('email').value.trim();
                const pw = document.getElementById('password').value;
                if (!email) { errorEl.textContent = 'Please enter your email.'; errorEl.style.display = 'block'; }
                else if (!pw) { errorEl.textContent = 'Password is required.'; errorEl.style.display = 'block'; }
            });

            // Password visibility toggle
            const pwInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');
            const eyeOpenSVG = `
                <svg id="eyeOpen" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" />
                </svg>`;
            const eyeClosedSVG = `
                <svg id="eyeClosed" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.36 20.36 0 0 1 5.61-5.94" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 1l22 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;

            if (toggleBtn && pwInput) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = pwInput.type === 'password';
                    pwInput.type = isPassword ? 'text' : 'password';
                    toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                    toggleBtn.title = isPassword ? 'Hide password' : 'Show password';
                    toggleBtn.innerHTML = isPassword ? eyeClosedSVG : eyeOpenSVG;
                });
            }
        })();
    </script>
</body>

</html>