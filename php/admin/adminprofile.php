<?php
session_start();


$username = $email = $pic = $old_password = $new_password = $confirm_password = "";
$username_err = $email_err = $pic_err = $old_password_err = $new_password_err = $confirm_password_err = "";
$success_msg = "";


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['do']) && $_POST['do'] === 'reset') {
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_POST['do']) || $_POST['do'] !== 'reset')) {


    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }


    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }


    if (empty(trim($_POST["pic"]))) {
        $pic_err = "Please enter your profile picture.";
    } else {
        $pic = trim($_POST["pic"]);
    }

    if (empty(trim($_POST["old_password"]))) {
        $old_password_err = "Please enter your old password.";
    } else {
        $old_password = trim($_POST["old_password"]);
    }

    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 4) {
        $new_password_err = "Password must have at least 4 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }


    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm new password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    if (
        empty($username_err) &&
        empty($email_err) &&
        empty($pic_err) &&
        empty($old_password_err) &&
        empty($new_password_err) &&
        empty($confirm_password_err)
    ) {


        $success_msg = "Profile updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../css/adminprofile.css">
</head>

<body>
    <div class="container">

        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../php/admindash.php">Home</a></li>
                <li><a href="../php/adminorder.php">Orders</a></li>
                <li><a href="../php/adminproduct.php">Products</a></li>
                <li><a href="../php/adminuser.php">Users</a></li>
                <li><a href="../php/adminprofile.php" class="active">Profile</a></li>
            </ul>
        </nav>

        <div class="logout1">
            <a href="../php/admindash.php">Logout</a>
        </div>

        <h2 class="middletitle">UPDATE PROFILE</h2>

        <?php if (!empty($success_msg)): ?>
            <p style="color: green; font-weight: bold; text-align:center;"><?php echo $success_msg; ?></p>
        <?php endif; ?>

        <section class="update-product">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <!-- Profile image preview (static) -->
                <img src="../../resources/userphoto.jpg" alt="profile picture">

                <div class="flex">
                    <div class="inputBox">
                        <span>Username :</span>
                        <input type="text" name="username" class="box" value="<?php echo $username; ?>" placeholder="Enter username">
                        <p style="color: red;"><?php echo $username_err; ?></p>

                        <span>Email :</span>
                        <input type="email" name="email" class="box" value="<?php echo $email; ?>" placeholder="Enter email">
                        <p style="color: red;"><?php echo $email_err; ?></p>

                        <span>Update Picture :</span>
                        <input type="file" name="pic" class="box" accept="image/jpg, image/jpeg, image/png">
                    </div>

                    <div class="inputBox">
                        <span>Old Password :</span>
                        <input type="password" name="old_password" class="box" placeholder="Enter old password">
                        <p style="color: red;"><?php echo $old_password_err; ?></p>

                        <span>New Password :</span>
                        <input type="password" name="new_password" class="box" placeholder="Enter new password">
                        <p style="color: red;"><?php echo $new_password_err; ?></p>

                        <span>Confirm Password :</span>
                        <input type="password" name="confirm_password" class="box" placeholder="Confirm new password">
                        <p style="color: red;"><?php echo $confirm_password_err; ?></p>
                    </div>
                </div>

                <div class="btn">
                    <input type="submit" value="Update Profile" class="btn">

                    <button type="submit" name="do" value="reset" class="btn">Reset</button>
                </div>
            </form>
        </section>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a></p>
                    <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                    <p>Address: 123 Skyline Avenue, Dhaka</p>
                </div>
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>We are passionate about serving the finest coffee, crafted with love and expertise. Join us for a unique coffee experience!</p>
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
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook" class="social-logo" />
                        </a>
                        <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram" class="social-logo" />
                        </a>
                        <a href="https://x.com" class="social-icon" aria-label="X">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" alt="X" class="social-logo" />
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
        document.addEventListener("DOMContentLoaded", () => {

            const picInput = document.querySelector('input[name="pic"]');
            const imgEl = document.querySelector('section.update-product img');

            picInput?.addEventListener("change", e => {
                const file = e.target.files[0];
                if (file && file.type.startsWith("image/")) {
                    imgEl.src = URL.createObjectURL(file);
                }
            });


            const form = document.querySelector("form");
            form?.addEventListener("submit", e => {
                const username = form.username.value.trim();
                const email = form.email.value.trim();
                const newPass = form.new_password.value;
                const conPass = form.confirm_password.value;

                if (!username) {
                    alert("Username is required");
                    e.preventDefault();
                } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                    alert("Enter valid email");
                    e.preventDefault();
                } else if (newPass && newPass !== conPass) {
                    alert("Passwords do not match");
                    e.preventDefault();
                }
            });

            const logoutLink = document.querySelector(".logout1 a");
            if (logoutLink) {
                logoutLink.addEventListener("click", function(e) {
                    if (!confirm("Are you sure you want to logout?")) {
                        e.preventDefault();
                    }
                });
            }


            const formBtn = document.querySelector(".newsletter-btn");
            formBtn.addEventListener("click", function() {
                const email = document.querySelector(".newsletter-input").value.trim();
                if (!email) {
                    alert("Please enter your email!");
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    alert("Please enter a valid email!");
                } else {
                    alert("Thank you for subscribing, " + email + "!");
                }
            });
        });
    </script>

</body>

</html>