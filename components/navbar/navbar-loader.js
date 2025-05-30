// navbar-loader.js - Universal navbar loader
class NavbarLoader {
    constructor() {
        this.navbarLoaded = false;
    }
    
    fixNavbarPaths() {
        const currentPath = window.location.pathname;
        const navbarContainer = document.getElementById('navbar-container');
        
        if (!navbarContainer) return;
        
        // Determine the base path relative to current location
        let basePath = '/';
        if (currentPath.includes('/blog/')) {
            basePath = '../';
        } else if (currentPath.includes('/docs/') || currentPath.includes('/product/')) {
            basePath = '../';
        }
        
        // Only fix paths if we're in a subdirectory
        if (basePath !== '/') {
            const links = navbarContainer.querySelectorAll('a[href^="/"]');
            links.forEach(link => {
                const href = link.getAttribute('href');
                if (href.startsWith('/') && !href.startsWith('//')) {
                    // Convert absolute paths to relative paths
                    let newPath = href.substring(1); // Remove leading slash
                    if (newPath === '') newPath = 'index.html';
                    link.setAttribute('href', basePath + newPath);
                }
            });
            
            // Special handling for blog link
            const blogLink = navbarContainer.querySelector('#nav-blog');
            if (blogLink && currentPath.includes('/blog/')) {
                blogLink.setAttribute('href', 'blog.php');
            }
        }
    }
    
    async loadNavbar() {
        if (this.navbarLoaded) return;
        
        try {
            const navbarContainer = document.getElementById('navbar-container');
            if (!navbarContainer) {
                console.error('Navbar container not found');
                return;
            }
            
            // Determine the correct path to navbar based on current location
            const currentPath = window.location.pathname;
            let navbarPath;
            
            if (currentPath.includes('/docs/')) {
                navbarPath = '../components/navbar/navbar.html';
            } else if (currentPath.includes('/product/')) {
                navbarPath = '../components/navbar/navbar.html';
            } else if (currentPath.includes('/blog/')) {
                navbarPath = '../components/navbar/navbar.html';
            } else {
                navbarPath = './components/navbar/navbar.html';
            }
            
            const response = await fetch(navbarPath);
            if (!response.ok) {
                throw new Error(`Failed to load navbar: ${response.status}`);
            }
            
            const navbarHTML = await response.text();
            navbarContainer.innerHTML = navbarHTML;
            
            this.navbarLoaded = true;
            console.log('Navbar loaded successfully');
            
            // Initialize navbar functionality after loading
            if (typeof initializeNavbar === 'function') {
                initializeNavbar();
            }
            
            // Fix navbar paths based on current location
            this.fixNavbarPaths();
            
        } catch (error) {
            console.error('Error loading navbar:', error);
            this.showFallbackNavbar();
        }
    }
    
    showFallbackNavbar() {
        // Simple fallback - just in case
        const navbarContainer = document.getElementById('navbar-container');
        if (navbarContainer) {
            navbarContainer.innerHTML = `
                <header class="navbar-header" style="background: #4f46e5; color: white; padding: 1rem;">
                    <div class="container">
                        <nav style="display: flex; justify-content: space-between; align-items: center;">
                            <a href="/" style="color: white; text-decoration: none; font-weight: bold;">Selenix.io</a>
                            <div>
                                <a href="/docs/index.html" style="color: white; margin: 0 1rem;">Docs</a>
                                <a href="/product/templates/index.html" style="color: white; margin: 0 1rem;">Templates</a>
                            </div>
                        </nav>
                    </div>
                </header>
            `;
        }
    }
}

// Global navbar loader instance
window.navbarLoader = new NavbarLoader();

// Auto-load navbar when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.navbarLoader.loadNavbar();
});
