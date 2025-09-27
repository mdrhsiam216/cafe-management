<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Cafe Menu - Skyline Coffee Shop</title>
		<link rel="stylesheet" href="css/menu.css" />
		<link
			href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
			rel="stylesheet"
		/>
	</head>
	<body>
		<div class="container">
			<nav class="navbar">
				<ul class="nav-links">
					<li><a href="index.php">Home</a></li>
					<li><a href="php/menu.php">Menu</a></li>
					<li><a href="#about-section">About</a></li>
					<li><a href="#contact-section">Contact</a></li>
					<li><a href="php/customer/profile.php">Profile</a></li>
				</ul>
			</nav>
			<div class="welcome-box">
				<div class="hero-section">
					<img
						src="resources/Brown Modern Circle Coffee Shop Logo.png"
						alt="Cafe Logo"
						class="logo"
					/>
					<h1>Welcome to Skyline Coffee Shop</h1>
					<p>Discover amazing coffee experiences and exclusive offers!</p>
					<div class="action-buttons">
						<a href="php/login.php"
							><button class="btn">Login</button></a
						>
						<a href="php/menu.php"
							><button class="btn">View Menu</button></a
						>
					</div>
				</div>
				<section class="offers-section">
					<h2>Special Offers</h2>
					<div class="offer-grid">
						<div class="offer-card">
							<div class="offer-badge">20% OFF</div>
							<img
								src="resources/coffee.png"
								alt="Premium Coffee"
								class="offer-img"
							/>
							<h3>Premium Coffee Blend</h3>
							<p class="offer-description">
								Rich, aromatic espresso blend crafted from the finest beans
							</p>
							<div class="price-section">
								<span class="original-price">৳180</span>
								<span class="discounted-price">৳144</span>
							</div>
							<a href="php/menu.php"
								><button class="btn order-btn">Order Now</button></a
							>
						</div>
						<div class="offer-card">
							<div class="offer-badge">Buy 2 Get 1</div>
							<img
								src="resources/Latte.png"
								alt="Signature Latte"
								class="offer-img"
							/>
							<h3>Signature Latte</h3>
							<p class="offer-description">
								Creamy espresso with perfectly steamed milk and beautiful latte
								art
							</p>
							<div class="price-section">
								<span class="offer-text">3 for ৳500</span>
							</div>
							<a href="php/menu.php"
								><button class="btn order-btn">Order Now</button></a
							>
						</div>
						<div class="offer-card">
							<div class="offer-badge">15% OFF</div>
							<img
								src="resources/Croissant.png"
								alt="Fresh Croissants"
								class="offer-img"
							/>
							<h3>Fresh Croissants</h3>
							<p class="offer-description">
								Freshly baked, buttery croissants perfect with your morning
								coffee
							</p>
							<div class="price-section">
								<span class="original-price">৳220</span>
								<span class="discounted-price">৳187</span>
							</div>
							<a href="php/menu.php"
								><button class="btn order-btn">Order Now</button></a
							>
						</div>
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
	</body>
</html>