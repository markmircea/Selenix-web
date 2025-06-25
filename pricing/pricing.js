// Pricing Page JavaScript Functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all pricing page functionality
    initializePricingPage();
    
    // Initialize pricing toggle functionality
    initializePricingToggle();
    
    // Initialize PayPal buttons
    initializePayPalButtons();
});

function initializePricingToggle() {
    const toggle = document.getElementById('pricing-toggle');
    const monthlyPrices = document.querySelectorAll('.price.monthly, .period.monthly');
    const yearlyPrices = document.querySelectorAll('.price.yearly, .period.yearly');
    const monthlyPayPal = document.querySelectorAll('.paypal-container.monthly');
    const yearlyPayPal = document.querySelectorAll('.paypal-container.yearly');

    if (toggle) {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                // Show yearly prices and PayPal buttons
                monthlyPrices.forEach(el => el.classList.add('hidden'));
                yearlyPrices.forEach(el => el.classList.remove('hidden'));
                monthlyPayPal.forEach(el => el.classList.add('hidden'));
                yearlyPayPal.forEach(el => el.classList.remove('hidden'));
            } else {
                // Show monthly prices and PayPal buttons
                yearlyPrices.forEach(el => el.classList.add('hidden'));
                monthlyPrices.forEach(el => el.classList.remove('hidden'));
                yearlyPayPal.forEach(el => el.classList.add('hidden'));
                monthlyPayPal.forEach(el => el.classList.remove('hidden'));
            }
        });
    }
}

function initializePayPalButtons() {
    // Load PayPal SDK
    if (!window.paypal) {
        const script = document.createElement('script');
        script.src = 'https://www.paypal.com/sdk/js?client-id=AT1AN3A-SZTy0CoeQzjZO-LMKVYZju4ABAIXr62BrXZ99Xt3bqkbuhXlTA5gj_sM1vskMxjKngLpqcyK&vault=true&intent=subscription';
        script.onload = function() {
            renderPayPalButtons();
        };
        document.head.appendChild(script);
    } else {
        renderPayPalButtons();
    }
}

function renderPayPalButtons() {
    // Monthly PayPal button (Professional Support - $49/month)
    if (document.getElementById('paypal-button-container-monthly')) {
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'blue',
                layout: 'vertical',
                label: 'subscribe',
                height: 45
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    plan_id: 'P-7G1214552U8556355NBN65EI' // Monthly plan ID
                });
            },
            onApprove: function(data, actions) {
                handleSubscriptionSuccess(data.subscriptionID, 'Monthly Professional Support');
            },
            onError: function(err) {
                handleSubscriptionError('Monthly subscription failed. Please try again.');
            },
            onCancel: function(data) {
                showMessage('Subscription cancelled. You can try again anytime.', 'error');
            }
        }).render('#paypal-button-container-monthly');
    }

    // Yearly PayPal button (Professional Support - $39/month billed yearly)
    if (document.getElementById('paypal-button-container-yearly')) {
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'blue',
                layout: 'vertical',
                label: 'subscribe',
                height: 45
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    plan_id: 'P-1TS66068XW105472GNBN67HA' // Yearly plan ID
                });
            },
            onApprove: function(data, actions) {
                handleSubscriptionSuccess(data.subscriptionID, 'Yearly Professional Support');
            },
            onError: function(err) {
                handleSubscriptionError('Yearly subscription failed. Please try again.');
            },
            onCancel: function(data) {
                showMessage('Subscription cancelled. You can try again anytime.', 'error');
            }
        }).render('#paypal-button-container-yearly');
    }
}

function handleSubscriptionSuccess(subscriptionID, planType) {
    // Show success message to user
    showMessage(`🎉 Subscription successful! Your subscription ID is: ${subscriptionID}. Welcome to Selenix Professional Support!`, 'success');
    
    // Send notification email
    sendSubscriptionNotification(subscriptionID, planType);
    
    // Optional: Track the conversion
    console.log('Subscription successful:', subscriptionID, planType);
}

function handleSubscriptionError(message) {
    showMessage(`❌ ${message}`, 'error');
}

function sendSubscriptionNotification(subscriptionID, planType) {
    const data = {
        subscriptionID: subscriptionID,
        planType: planType,
        userEmail: 'subscriber@example.com', // You might want to collect this in a form
        userName: 'New Subscriber',
        timestamp: new Date().toISOString()
    };

    fetch('./notify-subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Notification email sent successfully');
        } else {
            console.error('Failed to send notification email:', data.error);
        }
    })
    .catch(error => {
        console.error('Error sending notification:', error);
    });
}

function showMessage(message, type) {
    // Remove any existing message
    const existingMessage = document.querySelector('.subscription-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `subscription-message ${type}`;
    messageDiv.innerHTML = `
        <div class="message-content">
            <span class="message-text">${message}</span>
            <button class="message-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    // Append message to body for sticky positioning
    document.body.appendChild(messageDiv);
    
    // Trigger animation by adding visible class after a small delay
    setTimeout(() => {
        messageDiv.classList.add('visible');
    }, 100);
    
    // Auto-hide success messages after 8 seconds
    if (type === 'success') {
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.classList.remove('visible');
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }
        }, 8000);
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