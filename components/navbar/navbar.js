// Navbar Component JavaScript
function initializeNavbar() {
    // Sticky Header Effect
    const header = document.querySelector('header.navbar-header');
    
    if (header) {
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
    }

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
    
    // Set active state for current page
    const currentPath = window.location.pathname;
    
    // Handle main nav links
    const mainNavLinks = document.querySelectorAll('.nav-links > a');
    if (mainNavLinks) {
        mainNavLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href) && href !== '/') {
                link.classList.add('active');
            }
        });
    }
    
    // Handle product dropdown
    if (currentPath.includes('/product/')) {
        const productToggle = document.querySelector('.dropdown-toggle');
        if (productToggle) {
            productToggle.classList.add('active');
        }
        
        // Find and activate the specific product link
        const dropdownLinks = document.querySelectorAll('.dropdown-menu a');
        if (dropdownLinks) {
            dropdownLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.includes(href)) {
                    link.classList.add('active');
                }
            });
        }
    }
    
    console.log('Navbar initialized successfully');
}
