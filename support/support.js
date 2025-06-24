// Contact form functionality
document.addEventListener('DOMContentLoaded', function() {
    // Use a more specific selector to avoid conflicts
    const form = document.querySelector('form#contact-form-element');
    const submitBtn = document.getElementById('submit-btn');
    const messagesDiv = document.getElementById('form-messages');

    // Debug logging
    console.log('Form element:', form);
    console.log('Submit button:', submitBtn);
    console.log('Messages div:', messagesDiv);

    // Verify form element exists and is actually a form
    if (!form || form.tagName !== 'FORM') {
        console.error('Contact form not found or is not a form element');
        console.log('Available forms:', document.querySelectorAll('form'));
        return;
    }

    // Get button elements
    const submitIcon = submitBtn.querySelector('#submit-icon');
    const submitText = submitBtn.querySelector('#submit-text');
    
    // Create spinner element
    const spinner = document.createElement('i');
    spinner.className = 'fa-solid fa-spinner loading-spinner';
    spinner.style.display = 'none';
    submitBtn.insertBefore(spinner, submitText);

    function showMessage(message, type) {
        messagesDiv.textContent = message;
        messagesDiv.className = `form-messages ${type}`;
        messagesDiv.style.display = 'block';
        
        // Scroll to the message
        messagesDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                messagesDiv.style.display = 'none';
            }, 5000);
        }
    }

    function setLoading(loading) {
        if (loading) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitText.textContent = 'Sending...';
            submitIcon.style.display = 'none';
            spinner.style.display = 'inline-block';
        } else {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            submitText.textContent = 'Send Message';
            submitIcon.style.display = 'inline-block';
            spinner.style.display = 'none';
        }
    }

    // Add form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        console.log('Form element type:', form.constructor.name);
        console.log('Form tag name:', form.tagName);
        
        // Hide any previous messages
        messagesDiv.style.display = 'none';
        
        // Show loading state
        setLoading(true);
        
        try {
            // Create FormData - add extra verification
            if (!(form instanceof HTMLFormElement)) {
                throw new Error('Form is not an HTMLFormElement');
            }
            
            const formData = new FormData(form);
            
            // Debug: log form data
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            // Send form data
            fetch('contact-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                setLoading(false);
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    form.reset(); // Clear the form
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                setLoading(false);
                console.error('Error:', error);
                showMessage('An error occurred while sending your message. Please try again or contact us directly at support@selenix.io', 'error');
            });
            
        } catch (error) {
            setLoading(false);
            console.error('FormData error:', error);
            showMessage('There was an error with the form. Please try refreshing the page.', 'error');
        }
    });
});
