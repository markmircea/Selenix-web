// Pricing Page JavaScript Functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all pricing page functionality
    initializePricingPage();
    
    // Initialize pricing toggle functionality
    initializePricingToggle();
});

function initializePricingToggle() {
    const toggle = document.getElementById('pricing-toggle');
    const monthlyPrices = document.querySelectorAll('.price.monthly, .period.monthly');
    const yearlyPrices = document.querySelectorAll('.price.yearly, .period.yearly');

    if (toggle) {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                // Show yearly prices
                monthlyPrices.forEach(el => el.classList.add('hidden'));
                yearlyPrices.forEach(el => el.classList.remove('hidden'));
            } else {
                // Show monthly prices
                yearlyPrices.forEach(el => el.classList.add('hidden'));
                monthlyPrices.forEach(el => el.classList.remove('hidden'));
            }
        });
    }
}

function initializePricingPage() {
    // Initialize smooth scrolling for anchor links
    initializeSmoothScrolling();
    
    // Initialize scroll animations for elements
    initializeScrollAnimations();
    
    // Initialize plan card interactions
    initializePlanCardInteractions();
    
    // Initialize FAQ interactions (if needed)
    initializeFAQInteractions();
    
    // Initialize contact form handling (if contact form exists)
    initializeContactForm();
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Scroll animations for elements coming into view
function initializeScrollAnimations() {
    // Create intersection observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe all elements that should animate
    const animateElements = document.querySelectorAll('.plan-card, .faq-item, .comparison-table');
    animateElements.forEach(el => {
        observer.observe(el);
    });
    
    // Add CSS for animation (if not already in CSS file)
    if (!document.querySelector('#scroll-animations-style')) {
        const style = document.createElement('style');
        style.id = 'scroll-animations-style';
        style.textContent = `
            .plan-card, .faq-item, .comparison-table {
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }
            
            .plan-card.animate-in, .faq-item.animate-in, .comparison-table.animate-in {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);
    }
}

// Enhanced plan card interactions
function initializePlanCardInteractions() {
    const planCards = document.querySelectorAll('.plan-card');
    
    planCards.forEach(card => {
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = this.classList.contains('popular') ? 
                'scale(1.02) translateY(-8px)' : 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = this.classList.contains('popular') ? 
                'scale(1.02) translateY(0)' : 'translateY(0)';
        });
        
        // Add click analytics (optional)
        const planButton = card.querySelector('.plan-button');
        if (planButton) {
            planButton.addEventListener('click', function(e) {
                const planName = card.querySelector('h3').textContent;
                
                // Track plan selection (you can integrate with analytics here)
                console.log(`Plan selected: ${planName}`);
                
                // Add a subtle feedback animation
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 100);
            });
        }
    });
}

// FAQ interactions (expandable if needed in future)
function initializeFAQInteractions() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        item.addEventListener('click', function() {
            // Add a subtle pulse effect when clicked
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
}

// Contact form handling (if contact form exists)
function initializeContactForm() {
    const contactForm = document.querySelector('#contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const formObject = {};
            formData.forEach((value, key) => {
                formObject[key] = value;
            });
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Sending...';
            submitButton.disabled = true;
            
            // Simulate form submission (replace with actual form handling)
            setTimeout(() => {
                // Reset button
                submitButton.textContent = originalText;
                submitButton.disabled = false;
                
                // Show success message
                showNotification('Thank you! We\'ll get back to you soon.', 'success');
                
                // Reset form
                this.reset();
            }, 2000);
        });
    }
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '1rem 1.5rem',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '600',
        zIndex: '9999',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        backgroundColor: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'
    });
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after delay
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Plan comparison functionality
function initializePlanComparison() {
    const comparisonTable = document.querySelector('.comparison-table');
    
    if (comparisonTable) {
        // Add hover effects to comparison rows
        const comparisonRows = comparisonTable.querySelectorAll('.comparison-row');
        
        comparisonRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8fafc';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    }
}

// Initialize plan comparison when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializePlanComparison();
});

// Analytics tracking functions (optional)
function trackPlanView(planName) {
    // Integrate with your analytics service
    console.log(`Plan viewed: ${planName}`);
}

function trackPlanSelection(planName) {
    // Integrate with your analytics service
    console.log(`Plan selected: ${planName}`);
}

// Utility function to handle external links
function handleExternalLinks() {
    const externalLinks = document.querySelectorAll('a[href^="http"]');
    
    externalLinks.forEach(link => {
        if (!link.href.includes(window.location.hostname)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });
}

// Initialize external links handling
document.addEventListener('DOMContentLoaded', handleExternalLinks);