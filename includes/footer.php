</main>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <h3>EduPool</h3>
                    <p>Your gateway to quality education in Nepal. Discover universities, colleges, and courses that shape your future.</p>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseURL; ?>/">Home</a></li>
                    <li><a href="<?php echo $baseURL; ?>/pages/universities/">Universities</a></li>
                    <li><a href="<?php echo $baseURL; ?>/pages/colleges/">Colleges</a></li>
                    <li><a href="<?php echo $baseURL; ?>/pages/courses/">Courses</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>For Students</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseURL; ?>/register.php">Create Account</a></li>
                    <li><a href="<?php echo $baseURL; ?>/login.php">Student Login</a></li>
                    <li><a href="<?php echo $baseURL; ?>/#search">Search Courses</a></li>
                    <li><a href="<?php echo $baseURL; ?>/help.php">Help & Support</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Info</h4>
                <div class="contact-info">
                    <p><strong>Email:</strong> info@edupool.com.np</p>
                    <p><strong>Phone:</strong> +977-1-4444444</p>
                    <p><strong>Address:</strong> Kathmandu, Nepal</p>
                </div>
                <div class="social-links">
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Twitter</a>
                    <a href="#" class="social-link">LinkedIn</a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> EduPool. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="<?php echo $baseURL; ?>/privacy.php">Privacy Policy</a>
                    <a href="<?php echo $baseURL; ?>/terms.php">Terms of Service</a>
                    <a href="<?php echo $baseURL; ?>/admin/login.php">Admin Login</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const navMenu = document.getElementById('navMenu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });
            }
            
            // Dropdown menus
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                if (toggle && menu) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        dropdown.classList.toggle('active');
                    });
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!dropdown.contains(e.target)) {
                            dropdown.classList.remove('active');
                        }
                    });
                }
            });
            
            // Smooth scrolling for anchor links
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        e.preventDefault();
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Search form enhancement
            const searchForm = document.querySelector('.search-form');
            if (searchForm) {
                const searchInput = searchForm.querySelector('input[type="text"]');
                const searchButton = searchForm.querySelector('button[type="submit"]');
                
                if (searchInput && searchButton) {
                    searchInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            searchForm.submit();
                        }
                    });
                }
            }
            
            // Form validation enhancement
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('error');
                            isValid = false;
                        } else {
                            field.classList.remove('error');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
            
            // Image lazy loading
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
            
            // Back to top button
            const backToTopButton = document.createElement('button');
            backToTopButton.innerHTML = '↑';
            backToTopButton.className = 'back-to-top';
            backToTopButton.style.display = 'none';
            document.body.appendChild(backToTopButton);
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });
            
            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
