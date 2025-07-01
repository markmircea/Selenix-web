/**
 * Professional Services Page JavaScript
 * Handles interactive functionality for the custom template development page
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Professional Services page loaded');
    
    // Initialize all functionality
    initSmoothScrolling();
    initFAQFunctionality();
    initAnimations();
    initContactFormEnhancements();
    initContactFormSubmission();
    initPricingAnimations();
    initProcessStepAnimations();
    
    // Load components after DOM is ready
    setTimeout(() => {
        if (window.initializeNavbar) {
            window.initializeNavbar();
        }
        if (window.initializeFooter) {
            window.initializeFooter();
        }
    }, 100);
});

/**
 * Smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    const scrollLinks = document.querySelectorAll('.scroll-to, a[href^="#"]');
    
    scrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only handle internal anchor links
            if (href && href.startsWith('#')) {
                e.preventDefault();
                
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    // Calculate offset for fixed navbar
                    const navbarHeight = 80; // Approximate navbar height
                    const targetPosition = targetElement.offsetTop - navbarHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    
                    // Add a subtle highlight effect to the target section
                    targetElement.style.transition = 'background-color 0.3s ease';
                    targetElement.style.backgroundColor = 'rgba(79, 70, 229, 0.05)';
                    
                    setTimeout(() => {
                        targetElement.style.backgroundColor = '';
                    }, 2000);
                }
            }
        });
    });
}

/**
 * FAQ Accordion functionality
 */
function initFAQFunctionality() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const icon = question.querySelector('i');
        
        if (!question || !answer || !icon) return;
        
        question.addEventListener('click', () => {
            const isOpen = item.classList.contains('active');
            
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    const otherIcon = otherItem.querySelector('.faq-question i');
                    
                    if (otherAnswer) otherAnswer.style.maxHeight = null;
                    if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle current item
            if (!isOpen) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
                icon.style.transform = 'rotate(180deg)';
                
                // Add analytics tracking
                trackFAQInteraction(question.querySelector('h4').textContent);
            } else {
                item.classList.remove('active');
                answer.style.maxHeight = null;
                icon.style.transform = 'rotate(0deg)';
            }
        });
        
        // Add keyboard navigation
        question.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                question.click();
            }
        });
        
        // Make focusable for accessibility
        question.setAttribute('tabindex', '0');
        question.setAttribute('role', 'button');
        question.setAttribute('aria-expanded', 'false');
    });
}

/**
 * Intersection Observer for scroll animations
 */
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                
                // Special handling for different element types
                if (entry.target.classList.contains('service-card')) {
                    animateServiceCard(entry.target);
                } else if (entry.target.classList.contains('process-step')) {
                    animateProcessStep(entry.target);
                } else if (entry.target.classList.contains('benefit-card')) {
                    animateBenefitCard(entry.target);
                }
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll(`
        .service-card,
        .process-step,
        .benefit-card,
        .use-case-card,
        .showcase-card,
        .section-header
    `);
    
    animatedElements.forEach(el => {
        el.classList.add('animate-target');
        observer.observe(el);
    });
    
    // Add CSS for animations
    addAnimationStyles();
}

/**
 * Add CSS styles for animations
 */
function addAnimationStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .animate-target {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-target.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .service-card.animate-in {
            animation: slideInUp 0.6s ease forwards;
        }
        
        .process-step.animate-in {
            animation: slideInLeft 0.6s ease forwards;
        }
        
        .benefit-card.animate-in {
            animation: fadeInScale 0.6s ease forwards;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Animate service cards with staggered delay
 */
function animateServiceCard(card) {
    const cards = document.querySelectorAll('.service-card');
    const index = Array.from(cards).indexOf(card);
    card.style.animationDelay = `${index * 0.1}s`;
}

/**
 * Animate process steps
 */
function animateProcessStep(step) {
    const steps = document.querySelectorAll('.process-step');
    const index = Array.from(steps).indexOf(step);
    step.style.animationDelay = `${index * 0.2}s`;
}

/**
 * Animate benefit cards
 */
function animateBenefitCard(card) {
    const cards = document.querySelectorAll('.benefit-card');
    const index = Array.from(cards).indexOf(card);
    card.style.animationDelay = `${index * 0.15}s`;
}

/**
 * Contact form enhancements
 */
function initContactFormEnhancements() {
    const contactMethods = document.querySelectorAll('.contact-method');
    
    // Add click-to-copy functionality for contact methods
    contactMethods.forEach(method => {
        const emailSpan = method.querySelector('span');
        if (emailSpan && emailSpan.textContent.includes('@')) {
            method.style.cursor = 'pointer';
            method.title = 'Click to copy email address';
            
            method.addEventListener('click', () => {
                copyToClipboard(emailSpan.textContent);
                showCopyNotification('Email address copied to clipboard!');
            });
        }
    });
}

/**
 * Contact form submission handling
 */
function initContactFormSubmission() {
    const contactForm = document.getElementById('contact-form');
    const messagesContainer = document.getElementById('contact-form-messages');
    
    if (!contactForm) {
        console.log('Contact form not found');
        return;
    }
    
    console.log('Contact form found and event listener being added');
    
    // Check for URL parameters on page load
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');
    
    if (status && message) {
        showFormMessage(decodeURIComponent(message), status === 'success');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        // Clear previous errors
        clearFormErrors();
        
        const submitBtn = contactForm.querySelector('.submit-btn');
        const originalBtnContent = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner loading-spinner"></i> <span>Sending...</span>';
        submitBtn.classList.add('loading');
        
        // Create FormData from the form element
        const formData = new FormData();
        
        // Manually add form fields
        const formElements = contactForm.elements;
        for (let element of formElements) {
            if (element.name && element.value) {
                if (element.type === 'checkbox') {
                    if (element.checked) {
                        formData.append(element.name, 'on');
                    }
                } else {
                    formData.append(element.name, element.value);
                }
            }
        }
        
        formData.append('ajax', '1');
        
        console.log('Sending form data...');
        
        fetch('contact-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Result:', result);
            
            if (result.success) {
                showFormMessage(result.message, true);
                contactForm.reset();
                
                // Show confirmation modal
                showConfirmationModal();
                
                // Scroll to success message
                if (messagesContainer) {
                    messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Track successful submission
                trackFormSubmission('success');
                
            } else {
                if (result.errors && Object.keys(result.errors).length > 0) {
                    showFieldErrors(result.errors);
                }
                showFormMessage(result.message || 'There was an error processing your request.', false);
                
                // Track failed submission
                trackFormSubmission('error', result.message);
            }
            
        })
        .catch(error => {
            console.error('Form submission error:', error);
            showFormMessage('There was an error sending your request. Please try again or email us directly at support@selenix.io', false);
            trackFormSubmission('error', 'Network error: ' + error.message);
        })
        .finally(() => {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
            submitBtn.classList.remove('loading');
        });
    });
    
    // Real-time validation
    const requiredFields = contactForm.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            // Clear error state when user starts typing
            if (this.parentElement.classList.contains('error')) {
                this.parentElement.classList.remove('error');
            }
        });
    });
}

/**
 * Show form message
 */
function showFormMessage(message, isSuccess = true) {
    const messagesContainer = document.getElementById('contact-form-messages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `form-message ${isSuccess ? 'success' : 'error'}`;
    messageDiv.innerHTML = `
        <i class="fa-solid fa-${isSuccess ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    messagesContainer.innerHTML = '';
    messagesContainer.appendChild(messageDiv);
    
    // Auto-hide success messages after 10 seconds
    if (isSuccess) {
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 300);
        }, 10000);
    }
}

/**
 * Show field-specific errors
 */
function showFieldErrors(errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            const formGroup = field.parentElement;
            const errorMessage = formGroup.querySelector('.error-message');
            
            formGroup.classList.add('error');
            if (errorMessage) {
                errorMessage.textContent = errors[fieldName];
            }
        }
    });
}

/**
 * Clear all form errors
 */
function clearFormErrors() {
    const errorGroups = document.querySelectorAll('.form-group.error');
    errorGroups.forEach(group => {
        group.classList.remove('error');
    });
    
    const messagesContainer = document.getElementById('contact-form-messages');
    if (messagesContainer) {
        messagesContainer.innerHTML = '';
    }
}

/**
 * Validate individual field
 */
function validateField(field) {
    const formGroup = field.parentElement;
    const errorMessage = formGroup.querySelector('.error-message');
    
    let isValid = true;
    let errorText = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !field.value.trim()) {
        isValid = false;
        errorText = `${field.previousElementSibling.textContent.replace('*', '').trim()} is required`;
    }
    
    // Email validation
    if (field.type === 'email' && field.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value.trim())) {
            isValid = false;
            errorText = 'Please enter a valid email address';
        }
    }
    
    // URL validation
    if (field.type === 'url' && field.value.trim()) {
        try {
            new URL(field.value.trim());
        } catch {
            isValid = false;
            errorText = 'Please enter a valid URL (including https://)';
        }
    }
    
    // Update UI
    if (isValid) {
        formGroup.classList.remove('error');
    } else {
        formGroup.classList.add('error');
        if (errorMessage) {
            errorMessage.textContent = errorText;
        }
    }
    
    return isValid;
}

/**
 * Show confirmation modal
 */
function showConfirmationModal() {
    // Create modal HTML
    const modalHTML = `
        <div class="confirmation-modal-overlay">
            <div class="confirmation-modal">
                <div class="modal-header">
                    <div class="success-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <h3>Request Sent Successfully!</h3>
                </div>
                <div class="modal-body">
                    <p>Thank you for your interest in our professional template development services!</p>
                    <div class="confirmation-details">
                        <div class="detail-item">
                            <i class="fa-solid fa-clock"></i>
                            <span>We'll review your requirements and respond within <strong>24 hours</strong></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Check your email for a confirmation copy</span>
                        </div>
                        <div class="detail-item">
                            <i class="fa-solid fa-phone"></i>
                            <span>For urgent requests, email us directly at <strong>support@selenix.io</strong></span>
                        </div>
                    </div>
                    <div class="next-steps">
                        <h4>What happens next?</h4>
                        <ol>
                            <li>Our team reviews your automation requirements</li>
                            <li>We prepare a detailed quote and timeline</li>
                            <li>We send you a personalized proposal</li>
                            <li>Upon approval, we start building your custom template</li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="primary-button close-modal">
                        <i class="fa-solid fa-check"></i>
                        Got it, thanks!
                    </button>
                    <a href="../templates/" class="secondary-outline-button">
                        <i class="fa-solid fa-eye"></i>
                        Browse Example Templates
                    </a>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Get modal elements
    const modalOverlay = document.querySelector('.confirmation-modal-overlay');
    const modal = document.querySelector('.confirmation-modal');
    const closeBtn = document.querySelector('.close-modal');
    
    // Show modal with animation
    setTimeout(() => {
        modalOverlay.style.opacity = '1';
        modal.style.transform = 'translateY(0) scale(1)';
    }, 10);
    
    // Close modal function
    function closeModal() {
        modalOverlay.style.opacity = '0';
        modal.style.transform = 'translateY(-50px) scale(0.95)';
        
        setTimeout(() => {
            if (modalOverlay.parentNode) {
                modalOverlay.parentNode.removeChild(modalOverlay);
            }
        }, 300);
    }
    
    // Event listeners
    closeBtn.addEventListener('click', closeModal);
    
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    });
    
    // Auto-close after 30 seconds
    setTimeout(() => {
        if (document.querySelector('.confirmation-modal-overlay')) {
            closeModal();
        }
    }, 30000);
}

/**
 * Track form submission for analytics
 */
function trackFormSubmission(status, errorMessage = null) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submission', {
            'form_name': 'professional_services_contact',
            'submission_status': status,
            'error_message': errorMessage,
            'page_title': 'Professional Services'
        });
    }
    
    console.log('Form submission tracked:', status, errorMessage);
}

/**
 * Pricing card animations
 */
function initPricingAnimations() {
    const priceCard = document.querySelector('.price-card');
    
    if (priceCard) {
        // Add hover effect enhancement
        priceCard.addEventListener('mouseenter', () => {
            priceCard.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        priceCard.addEventListener('mouseleave', () => {
            priceCard.style.transform = '';
        });
        
        // Animate numbers when in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animatePriceNumber();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(priceCard);
    }
}

/**
 * Animate price number
 */
function animatePriceNumber() {
    const amountElement = document.querySelector('.amount');
    if (!amountElement) return;
    
    const finalValue = 200;
    const duration = 1000;
    const startTime = Date.now();
    
    function updateNumber() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.floor(progress * finalValue);
        
        amountElement.textContent = current;
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        } else {
            amountElement.textContent = finalValue;
        }
    }
    
    amountElement.textContent = '0';
    requestAnimationFrame(updateNumber);
}

/**
 * Process step animations
 */
function initProcessStepAnimations() {
    const processSteps = document.querySelectorAll('.process-step');
    
    processSteps.forEach((step, index) => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('step-visible');
                        animateStepLine(entry.target);
                    }, index * 200);
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(step);
    });
    
    // Add CSS for step animations
    const style = document.createElement('style');
    style.textContent = `
        .process-step {
            opacity: 0;
            transform: translateX(-30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .process-step.step-visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .step-line {
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.8s ease 0.3s;
        }
        
        .process-step.step-visible .step-line {
            transform: scaleY(1);
        }
    `;
    document.head.appendChild(style);
}

/**
 * Animate step connecting line
 */
function animateStepLine(step) {
    const line = step.querySelector('.step-line');
    if (line) {
        line.style.transform = 'scaleY(1)';
    }
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (error) {
            console.error('Failed to copy text:', error);
        }
        
        document.body.removeChild(textArea);
    }
}

/**
 * Show copy notification
 */
function showCopyNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'copy-notification';
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--primary-gradient);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

/**
 * Analytics tracking functions
 */
function trackFAQInteraction(question) {
    // Example analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'faq_interaction', {
            'faq_question': question,
            'page_title': 'Professional Services'
        });
    }
    
    console.log('FAQ interaction:', question);
}

function trackEmailContact() {
    // Example analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'email_contact', {
            'contact_method': 'email_button',
            'page_title': 'Professional Services'
        });
    }
    
    console.log('Email contact initiated');
}

/**
 * Scroll progress indicator
 */
function initScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: var(--primary-gradient);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (window.pageYOffset / totalHeight) * 100;
        progressBar.style.width = Math.min(progress, 100) + '%';
    });
}

/**
 * Initialize scroll progress on load
 */
window.addEventListener('load', () => {
    initScrollProgress();
});

/**
 * Handle window resize for responsive adjustments
 */
window.addEventListener('resize', debounce(() => {
    // Recalculate any layout-dependent elements
    const faqAnswers = document.querySelectorAll('.faq-item.active .faq-answer');
    faqAnswers.forEach(answer => {
        answer.style.maxHeight = answer.scrollHeight + 'px';
    });
}, 250));

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Lazy loading for images (if any are added later)
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
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
}

/**
 * Error handling for component loading
 */
window.addEventListener('error', (e) => {
    console.error('Page error:', e.error);
    
    // Try to gracefully handle component loading failures
    if (e.error && e.error.message && e.error.message.includes('component')) {
        console.log('Attempting to recover from component loading error...');
        
        setTimeout(() => {
            if (window.initializeNavbar && !document.querySelector('.navbar')) {
                window.initializeNavbar();
            }
            if (window.initializeFooter && !document.querySelector('.footer')) {
                window.initializeFooter();
            }
        }, 1000);
    }
});

/**
 * Export functions for potential external use
 */
window.ProfessionalServicesPage = {
    initSmoothScrolling,
    initFAQFunctionality,
    initAnimations,
    trackFAQInteraction,
    trackEmailContact,
    showCopyNotification
};
