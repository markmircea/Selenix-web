// Footer Component JavaScript
function initializeFooter() {
    // You can add any footer-specific initialization here
    // For example, updating the current year dynamically
    const yearSpan = document.querySelector('.footer-copyright-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
    
    console.log('Footer initialized successfully');
}
