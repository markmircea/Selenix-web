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
    
    // Sticky Header Effect
    const header = document.querySelector('header');
    
    function updateHeaderStyle() {
        if (window.scrollY > 10) {
            header.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
        } else {
            header.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05)';
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
        }
    }
    
    // Initial header style
    updateHeaderStyle();
    
    // Update on scroll
    window.addEventListener('scroll', updateHeaderStyle);

    // Mobile Menu Toggle
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuButton && navLinks) {
        mobileMenuButton.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Toggle icon between bars and X
            const icon = this.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-xmark');
                } else {
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                }
            }
        });
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
                if (navLinks && navLinks.classList.contains('active')) {
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
});