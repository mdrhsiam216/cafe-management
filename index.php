<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Cafe Menu - Skyline Coffee Shop</title>
	<link rel="stylesheet" href="css/style.css" />
		<link
			href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
			rel="stylesheet"
		/>
	</head>
	<body>
		<?php
		// Show different header actions depending on login state
		session_start();
		$isLoggedIn = isset($_SESSION['user_id']);
		?>
		<div class="container">
			<nav class="navbar">
				<ul class="nav-links">
					<li><a href="index.php">Home</a></li>
					<li><a href="../cafe-management/php/customer/menu.php">Menu</a></li>
					<li><a href="#about-section">About</a></li>
					<li><a href="#contact-section">Contact</a></li>
					<li><a href="php/customer/profile.php">Profile</a></li>
					<li><a href="php/customer/cart.php">Cart</a></li>
					<?php if ($isLoggedIn): ?>
						<li ><a href="php/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
						
					<?php endif; ?>
				</ul>
			</nav>
			<div class="welcome-box">
				<div class="hero-section">
					<img
						src="./resources/Brown Modern Circle Coffee Shop Logo.png"
						alt="Cafe Logo"
						class="logo"
					/>
					<h1>Welcome to Skyline Coffee Shop</h1>
					<p>Discover amazing coffee experiences and exclusive offers!</p>
					<div class="action-buttons">
						<?php if ($isLoggedIn): ?>
							<a href="php/menu.php"><button class="btn">View Menu</button></a>
						<?php else: ?>
							<a href="php/login.php"><button class="btn">Login</button></a>
							<a href="php/menu.php"><button class="btn">View Menu</button></a>
						<?php endif; ?>
					</div>
				</div>
				<?php
				// Fetch special offers dynamically
				require_once 'db/db_connection.php';

				$special_offers = [];
				$query = "SELECT * FROM special_offers";
				if ($result = $conn->query($query)) {
					while ($row = $result->fetch_assoc()) {
						$special_offers[] = $row;
					}
					$result->free();
				} else {
					die('Error: ' . $conn->error);
				}
				?>

				<?php
				// Debugging: Log form submission
				if ($_SERVER['REQUEST_METHOD'] === 'POST') {
					file_put_contents('debug_log.txt', print_r($_POST, true), FILE_APPEND);
				}
				?>
				<section class="offers-section">
					<h2>Special Offers</h2>
					<div class="offer-grid">
						<?php if (empty($special_offers)): ?>
							<p>No special offers available at the moment.</p>
						<?php else: ?>
							<?php foreach ($special_offers as $offer): ?>
								<div class="offer-card">
									<div class="offer-badge">- <?php echo number_format($offer['discount'], 2); ?>%</div>
									<img
										src="<?php echo $offer['image']; ?>"
										alt="<?php echo $offer['title']; ?>"
										class="offer-img"
									/>
									<h3><?php echo $offer['title']; ?></h3>
									<p class="offer-description">
										<?php echo $offer['description']; ?>
									</p>
									<p class="offer-pricing">
										<span class="genuine-price">Original: <?php echo number_format($offer['genuine_price'], 2); ?></span>
										<span class="discounted-price">Now: <?php echo number_format($offer['genuine_price'] - ($offer['genuine_price'] * $offer['discount'] / 100), 2); ?></span>
									</p>
									<form method="POST" action="php/customer/order_special.php" class="order-special-form">
										<input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
										<button type="submit" class="btn order-btn">Order Now</button>
									</form>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</section>
				<section class="features-section">
					<h2>Why Choose Skyline Coffee Shop?</h2>
					<div class="features-grid">
						<div class="feature-item">
							<div class="feature-icon">☕</div>
							<h4>Premium Quality</h4>
							<p>Sourced from the finest coffee beans worldwide</p>
						</div>
						<div class="feature-item">
							<div class="feature-icon">⏒</div>
							<h4>Fresh Daily</h4>
							<p>Roasted fresh every morning for the perfect taste</p>
						</div>
						<div class="feature-item">
							<div class="feature-icon">👨‍🍳</div>
							<h4>Expert Baristas</h4>
							<p>Skilled craftsmen creating your perfect cup</p>
						</div>
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
	<script>
	// Intercept special offer order forms to show a confirm dialog
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.order-special-form').forEach(function(form) {
			form.addEventListener('submit', function(e) {
				var confirmed = confirm('Do you want to order this special offer now?');
				if (!confirmed) {
					e.preventDefault();
				}
			});
		});
	});

</script>
	</body>
</html>