<?php
require_once 'auth_middleware.php';
$isLoggedIn = isLoggedIn();
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Cafe Menu - Skyline Coffee Shop</title>
		<link rel="stylesheet" href="../css/menu.css" />
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
					<?php if ($isLoggedIn): ?>
						<?php if ($userRole === 'admin'): ?>
							<li><a href="admin/admindash.php">Dashboard</a></li>
						<?php elseif ($userRole === 'staff'): ?>
							<li><a href="staff/staff-orders.php">Dashboard</a></li>
						<?php elseif ($userRole === 'customer'): ?>
							<li><a href="customer/dashboard.php">Dashboard</a></li>
						<?php endif; ?>
						<li><a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
					<?php else: ?>
						<li><a href="login.php">Login</a></li>
						<li><a href="register.php">Register</a></li>
					<?php endif; ?>
				</ul>
			</nav>
			<div class="welcome-box">
				<div class="hero-section">
					<img
						src="../resources/Brown Modern Circle Coffee Shop Logo.png"
						alt="Cafe Logo"
						class="logo"
					/>
					<h1>Welcome to Skyline Coffee Shop</h1>
					<p>Explore our delicious menu and join us to place your order!</p>
				</div>
				<section class="menu-section">
					<h2>Our Menu</h2>
					<div class="action-buttons">
						<?php if ($isLoggedIn && $userRole === 'customer'): ?>
							<a href="customer/customer-order.php"><button class="btn">Order Now</button></a>
						<?php elseif (!$isLoggedIn): ?>
							<a href="login.php"><button class="btn">Login to Order</button></a>
						<?php endif; ?>
					</div>
					<table id="menu-table">
						<thead>
							<tr>
								<th>Photo</th>
								<th>Item</th>
								<th>Description</th>
								<th>Price (BDT)</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<img
										src="../resources/coffee.png"
										alt="Coffee"
										class="menu-img"
									/>
								</td>
								<td>Coffee</td>
								<td>Rich, aromatic espresso blend</td>
								<td>180</td>
							</tr>
							<tr>
								<td>
									<img src="../resources/Tea.png" alt="Tea" class="menu-img" />
								</td>
								<td>Tea</td>
								<td>Classic milk tea</td>
								<td>120</td>
							</tr>
							<tr>
								<td>
									<img src="../resources/Latte.png" alt="Latte" class="menu-img" />
								</td>
								<td>Latte</td>
								<td>Creamy espresso with steamed milk</td>
								<td>250</td>
							</tr>
							<tr>
								<td>
									<img
										src="../resources/Croissant.png"
										alt="Croissant"
										class="menu-img"
									/>
								</td>
								<td>Croissant</td>
								<td>Freshly baked croissants</td>
								<td>220</td>
							</tr>
						</tbody>
					</table>
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
		<script src="../WBT-Project/js/index.js"></script>
	</body>
</html>