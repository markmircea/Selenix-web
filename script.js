// Wait for DOM content to load before executing script
document.addEventListener('DOMContentLoaded', function() {
    // Preloader effect - add a simple loading screen
    const pageBody = document.body;
    pageBody.classList.add('loading');
    
    // Remove loading class after site loads
    window.addEventListener('load', function() {
        setTimeout(function() {
            pageBody.classList.remove('loading');
        }, 500);
    });
    
    // Reveal Elements on Scroll
    function revealElements() {
        const windowHeight = window.innerHeight;
        const revealPoint = 150;
        
        // Reveal text elements
        document.querySelectorAll('.reveal-text').forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            if (elementTop < windowHeight - revealPoint) {
                element.classList.add('active');
            }
        });
        
        // Fade in elements
        document.querySelectorAll('.fade-in-element').forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            if (elementTop < windowHeight - revealPoint) {
                element.classList.add('active');
            }
        });
    }
    
    // Run reveal on initial load
    revealElements();
    
    // Run reveal on scroll
    window.addEventListener('scroll', revealElements);
    
    // Add hover effects for feature items
    const featureItems = document.querySelectorAll('.feature-item');
    featureItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            const icon = this.querySelector('.feature-icon');
            if (icon) {
                icon.style.transform = 'scale(1.1)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = '';
            const icon = this.querySelector('.feature-icon');
            if (icon) {
                icon.style.transform = '';
            }
        });
    });
    
    // Counter animation function with easing effect
    function animateCounter(counter, target, duration = 2000) {
        let startValue = 0;
        let startTime = null;
        
        // Easing function for smoother animation
        function easeOutQuart(t) {
            return 1 - Math.pow(1 - t, 4);
        }
        
        function updateCounter(timestamp) {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOutQuart(progress);
            
            // Calculate current value based on easing
            const currentValue = Math.floor(easedProgress * target);
            
            // Format large numbers with commas
            const formattedValue = currentValue.toLocaleString();
            counter.textContent = formattedValue;
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString();
            }
        }
        
        requestAnimationFrame(updateCounter);
    }
    
    // Find and animate counters (if they exist)
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        // Check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom >= 0
            );
        }
        
        let hasAnimated = false;
        
        function checkCounters() {
            if (!hasAnimated) {
                counters.forEach(counter => {
                    if (isInViewport(counter)) {
                        const target = parseInt(counter.getAttribute('data-target') || '0', 10);
                        animateCounter(counter, target);
                        hasAnimated = true;
                    }
                });
            }
        }
        
        // Check on scroll
        window.addEventListener('scroll', checkCounters);
        
        // Initial check
        checkCounters();
    }
    
    // Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Offset for header
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navLinks = document.querySelector('.nav-links');
                const mobileMenuButton = document.querySelector('.mobile-menu-button');
                
                if (navLinks && navLinks.classList.contains('active') && mobileMenuButton) {
                    navLinks.classList.remove('active');
                    const icon = mobileMenuButton.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-xmark');
                        icon.classList.add('fa-bars');
                    }
                }
            }
        });
    });
});
