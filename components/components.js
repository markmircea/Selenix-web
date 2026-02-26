// Main Components Loader
document.addEventListener('DOMContentLoaded', function() {
    // Function to determine component path based on current location
    function getComponentPath(componentName) {
        // Get path depth to calculate relative path
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        const pathDepth = pathParts.length;
        
        // console.log('Path parts:', pathParts);
        // console.log('Path depth:', pathDepth);
        
        // Calculate how many levels up we need to go to reach the root
        let basePath = '';
        
        if (pathDepth === 0) {
            // We're at root (/)
            basePath = 'components/';
        } else if (pathDepth === 1) {
            // We're one level deep (/docs/ or /product/ or root-level pages like /pricing.html)
            basePath = '../components/';
        } else if (pathDepth === 2) {
            // We're two levels deep (/product/templates/ or /docs/subfolder/)
            basePath = '../../components/';
        } else {
            // For any deeper nesting, go up the appropriate number of levels
            basePath = '../'.repeat(pathDepth) + 'components/';
        }
        
        const finalPath = basePath + componentName;
        
        // console.log('Calculated base path:', basePath);
        // console.log('Final component path:', finalPath);
        
        return finalPath;
    }

    // Function to set active navigation states after navbar loads
    function setActiveNavigation() {
        const currentPath = window.location.pathname;
        const isDocsPage = currentPath.includes('/docs/');
        const isProductPage = currentPath.includes('/product/');
        const isPricingPage = currentPath.includes('/pricing');
        const isSupportPage = currentPath.includes('/support');
        const isBlogPage = currentPath.includes('/blog');
        
        console.log('Setting active navigation:', { 
            currentPath, 
            isDocsPage, 
            isProductPage, 
            isPricingPage, 
            isSupportPage, 
            isBlogPage 
        });
        
        // Remove all existing active classes
        document.querySelectorAll('.nav-links a, .dropdown-menu a, .dropdown-toggle').forEach(link => {
            link.classList.remove('active');
        });
        
        if (isDocsPage) {
            const docsLink = document.getElementById('nav-docs');
            if (docsLink) {
                docsLink.classList.add('active');
                // console.log('✅ Added active class to docs link');
            }
        } else if (isProductPage) {
            const productToggle = document.querySelector('.dropdown-toggle');
            if (productToggle) {
                productToggle.classList.add('active');
                // console.log('✅ Added active class to product dropdown');
            }
            
            // Check specific product pages
            const productPages = {
                'no-code-builder': 'nav-no-code-builder',
                'no-code-steps': 'nav-no-code-steps',
                'bot-runner': 'nav-bot-runner',
                'templates': 'nav-templates',
                'video-guides': 'nav-video-guides',
                'release-notes': 'nav-release-notes'
            };
            
            Object.keys(productPages).forEach(page => {
                if (currentPath.includes(page)) {
                    const element = document.getElementById(productPages[page]);
                    if (element) {
                        element.classList.add('active');
                        // console.log('✅ Added active class to', productPages[page]);
                    }
                }
            });
        } else if (isPricingPage) {
            const pricingLink = document.getElementById('nav-pricing');
            if (pricingLink) {
                pricingLink.classList.add('active');
                // console.log('✅ Added active class to pricing link');
            }
        } else if (isSupportPage) {
            const supportLink = document.getElementById('nav-support');
            if (supportLink) {
                supportLink.classList.add('active');
                // console.log('✅ Added active class to support link');
            }
        } else if (isBlogPage) {
            const blogLink = document.getElementById('nav-blog');
            if (blogLink) {
                blogLink.classList.add('active');
                // console.log('✅ Added active class to blog link');
            }
        }
    }
    
    // Add a debug console log
    // console.log('Components.js loaded. Current path:', window.location.pathname);
    
    // Load Navbar Component
    const navbarContainer = document.getElementById('navbar-container');
    if (navbarContainer) {
        const navbarPath = getComponentPath('navbar/navbar.html');
        // console.log('Loading navbar from:', navbarPath);
        
        fetch(navbarPath)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                navbarContainer.innerHTML = html;
                // console.log('Navbar loaded successfully');
                
                // Set active navigation after navbar is loaded and DOM is updated
                setTimeout(() => {
                    setActiveNavigation();
                    initializeNavbarFunctionality();
                }, 100);
            })
            .catch(error => {
                console.error('Error loading navbar:', error);
                navbarContainer.innerHTML = `
                    <div style="padding: 20px; background-color: #ff6b6b; color: white; border-radius: 5px; margin: 20px 0;">
                        <h2>Error Loading Navbar Component</h2>
                        <p>${error.message}</p>
                        <p>Path attempted: ${navbarPath}</p>
                        <p>Current location: ${window.location.href}</p>
                    </div>
                `;
            });
    }
    
    // Load Footer Component
    const footerContainer = document.getElementById('footer-container');
    if (footerContainer) {
        const footerPath = getComponentPath('footer/footer.html');
        // console.log('Loading footer from:', footerPath);
        
        fetch(footerPath)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                footerContainer.innerHTML = html;
                // Update copyright year dynamically
                const yearSpan = footerContainer.querySelector('.footer-copyright-year');
                if (yearSpan) {
                    yearSpan.textContent = new Date().getFullYear();
                }
            })
            .catch(error => {
                console.error('Error loading footer:', error);
                footerContainer.innerHTML = `
                    <div style="padding: 20px; background-color: #ff6b6b; color: white; border-radius: 5px; margin: 20px 0;">
                        <h2>Error Loading Footer Component</h2>
                        <p>${error.message}</p>
                        <p>Path attempted: ${footerPath}</p>
                        <p>Current location: ${window.location.href}</p>
                    </div>
                `;
            });
    }
    
    // Function to initialize navbar functionality (mobile menu, dropdowns, etc.)
    function initializeNavbarFunctionality() {
        // console.log('Initializing navbar functionality...');
        
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
            
            // Handle mobile dropdown toggles - Enhanced functionality
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
                        
                        // console.log('Mobile dropdown toggled:', dropdown.classList.contains('mobile-open'));
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
        
        // Enhanced dropdown functionality for desktop
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
        
        // console.log('Navbar functionality initialized successfully');
    }
});
