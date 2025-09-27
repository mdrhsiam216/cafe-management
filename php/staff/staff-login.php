<?php
// Simple server-side validation and demo authentication for staff login
session_start();

$errorMessage = '';
$staffIdValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic sanitization
    $staffId = isset($_POST['staffId']) ? trim($_POST['staffId']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']);

    $staffIdValue = htmlspecialchars($staffId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Validation rules
    if ($staffId === '') {
        $errorMessage = 'Please enter your Staff ID.';
    } elseif (!preg_match('/^S\d{3,}$/i', $staffId)) {
        // Example: Staff IDs like S123 or S1234
        $errorMessage = 'Staff ID must start with "S" followed by at least 3 digits.';
    } elseif ($password === '' || strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } else {
        // Demo authentication: replace this with real DB lookup in production
        // For demo accept staffId S1234 and password "password123"
        $validStaff = [
            'S1234' => 'password123',
            'S1000' => 'welcome1'
        ];

        $normalizedId = strtoupper($staffId);
        if (isset($validStaff[$normalizedId]) && $validStaff[$normalizedId] === $password) {
            // Successful login
            $_SESSION['staff_logged_in'] = true;
            $_SESSION['staff_id'] = $normalizedId;

            if ($remember) {
                // Set a simple remember cookie for demo purposes (do not store plaintext in production)
                setcookie('staff_remember', $normalizedId, time() + (30 * 24 * 60 * 60), '/');
            }

            // Redirect to staff orders page after successful login
            header('Location: staff-orders.php');
            exit;
        } else {
            $errorMessage = 'Invalid Staff ID or password.';
        }
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
    <link rel="stylesheet" href="../CSS/staff-orders.css">
    <link rel="icon" href="../Images/Brown Modern Circle Coffee Shop Logo.png">
</head>

<body>
    <div class="container">
        <div class="orders-box">
            <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo" />
            <h2>Staff Login</h2>

            <form id="staffLoginForm" class="login-form" action="" method="post" novalidate>
                <div class="form-group">
                    <label for="staffId">Staff ID</label>
                    <input id="staffId" name="staffId" type="text" required placeholder="e.g., S1234"
                        aria-required="true" value="<?php echo $staffIdValue; ?>" />
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
                const id = document.getElementById('staffId').value.trim();
                const pw = document.getElementById('password').value;
                if (!id) { errorEl.textContent = 'Please enter your Staff ID.'; errorEl.style.display = 'block'; }
                else if (!pw || pw.length < 6) { errorEl.textContent = 'Password must be at least 6 characters.'; errorEl.style.display = 'block'; }
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