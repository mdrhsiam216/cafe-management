<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Reset Password - Skyline Coffee Shop</title>
		    <link rel="stylesheet" href="../css/reset-password.css" />
		<link
			href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
			rel="stylesheet"
		/>
	</head>
	<body>
		<div class="container">
			<nav class="navbar">
				<ul class="nav-links">
					<li><a href="../index.php">Home</a></li>
					<li><a href="menu.php">Menu</a></li>
					<li><a href="#about-section">About</a></li>
					<li><a href="#contact-section">Contact</a></li>
					<li><a href="customer/profile.php">Profile</a></li>
				</ul>
			</nav>
			<div class="welcome-box">
				<div class="hero-section">
					<img
						src="../resources/Brown Modern Circle Coffee Shop Logo.png"
						alt="Cafe Logo"
						class="logo"
					/>
					<h1>Reset Password</h1>
					<p>Enter your email to receive a password reset link.</p>
				</div>
				<section class="reset-section">
					<form id="reset-form">
						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" required />
						</div>
						<button type="submit" class="btn">Send Reset Link</button>
					</form>
					<div class="links">
						<a href="login.php">Login</a>|
						<a href="../index.php">Back to Home</a>
					</div>
				</section>
				<footer class="footer">
					<div class="footer-content">
						<div class="footer-section" id="contact-section">
							<h3>Contact Us</h3>
							<p>
								Email:
								<a href="mailto:info@skylinecoffee.com"
									>info@skylinecoffee.com</a
								>
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
							<input
								type="email"
								placeholder="Enter your email"
								class="newsletter-input"
							/>
							<button class="btn newsletter-btn">Subscribe</button>
						</div>
						<div class="footer-section">
							<h3>Follow Us</h3>
							<div class="social-links">
								<a
									href="https://facebook.com"
									class="social-icon"
									aria-label="Facebook"
								>
									<img
										src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png"
										alt="Facebook Logo"
										class="social-logo"
									/>
								</a>
								<a
									href="https://instagram.com"
									class="social-icon"
									aria-label="Instagram"
								>
									<img
										src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png"
										alt="Instagram Logo"
										class="social-logo"
									/>
								</a>
								<a href="https://x.com" class="social-icon" aria-label="X">
									<img
										src="https://img.icons8.com/ios-filled/50/ffffff/x.png"
										class="social-logo"
									/>
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
	</body>
</html>