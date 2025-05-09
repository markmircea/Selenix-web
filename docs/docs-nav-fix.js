// Navigation fix script for docs pages
document.addEventListener('DOMContentLoaded', function() {
    console.log('Docs navigation fix script loaded');
    
    // Fix navigation links for docs pages
    function updateNavLinks() {
        // Find all navigation links in the navbar
        const navDocsLink = document.getElementById('nav-docs');
        const productLinks = document.querySelectorAll('.dropdown-menu a');
        
        // Make sure the docs link is marked as active
        if (navDocsLink) {
            navDocsLink.classList.add('active');
        }
        
        // Update product links to use relative paths
        productLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.startsWith('/product/')) {
                // Convert to relative path for docs directory
                link.setAttribute('href', '../product/' + href.split('/product/')[1]);
            }
        });
        
        // Update logo link to go to home
        const logo = document.querySelector('.logo');
        if (logo) {
            logo.setAttribute('href', '../index.html');
        }
        
        console.log('Navigation links updated for docs pages');
    }
    
    // Run the update after a short delay to ensure DOM is loaded
    setTimeout(updateNavLinks, 500);
    
    // Handle mobile menu toggle
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
});
