// navbar-loader.js - Universal navbar loader
class NavbarLoader {
    constructor() {
        this.navbarLoaded = false;
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
                <header style="background: #4f46e5; color: white; padding: 1rem;">
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
