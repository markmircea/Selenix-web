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
        
        // Handle mobile dropdown toggles - Updated to work properly
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdown = document.querySelector('.dropdown');
        
        if (dropdownToggle && dropdown) {
            dropdownToggle.addEventListener('click', function(e) {
                // Always prevent default for mobile
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Toggle the mobile-open class
                    dropdown.classList.toggle('mobile-open');
                    
                    // Update chevron icon rotation
                    const chevron = this.querySelector('i');
                    if (chevron) {
                        if (dropdown.classList.contains('mobile-open')) {
                            chevron.style.transform = 'rotate(180deg)';
                        } else {
                            chevron.style.transform = 'rotate(0deg)';
                        }
                    }
                    
                    console.log('Mobile dropdown toggled:', dropdown.classList.contains('mobile-open'));
                }
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('nav') && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                
                // Reset mobile menu icon
                const icon = mobileMenuButton.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                }
                
                // Close any open dropdowns
                if (dropdown) {
                    dropdown.classList.remove('mobile-open');
                    const chevron = dropdownToggle?.querySelector('i');
                    if (chevron) {
                        chevron.style.transform = 'rotate(0deg)';
                    }
                }
            }
        });
        
        // Handle window resize to clean up mobile states
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                // Reset mobile states when switching to desktop
                navLinks.classList.remove('active');
                if (dropdown) {
                    dropdown.classList.remove('mobile-open');
                }
                
                const icon = mobileMenuButton.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                }
                
                const chevron = dropdownToggle?.querySelector('i');
                if (chevron) {
                    chevron.style.transform = '';
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
    
    // Enhanced dropdown functionality
    const dropdown = document.querySelector('.dropdown');
    if (dropdown) {
        let dropdownTimeout;
        
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(dropdownTimeout);
            const menu = this.querySelector('.dropdown-menu');
            if (menu) {
                menu.style.opacity = '1';
                menu.style.pointerEvents = 'auto';
                menu.style.transform = 'translateY(0)';
            }
        });
        
        dropdown.addEventListener('mouseleave', function() {
            const menu = this.querySelector('.dropdown-menu');
            dropdownTimeout = setTimeout(() => {
                if (menu) {
                    menu.style.opacity = '0';
                    menu.style.pointerEvents = 'none';
                    menu.style.transform = 'translateY(10px)';
                }
            }, 100); // Small delay to allow mouse movement
        });
    }
    
    console.log('Navbar initialized successfully');
}
