// Main Components Loader
document.addEventListener('DOMContentLoaded', function() {
    // Get the base path for components
    const getBasePath = () => {
        // Get current script path to calculate the base path
        const scripts = document.getElementsByTagName('script');
        for (let i = 0; i < scripts.length; i++) {
            const src = scripts[i].src;
            if (src.includes('components/components.js')) {
                return src.substring(0, src.indexOf('components/components.js'));
            }
        }
        // Fallback to a relative path from current page
        const path = window.location.pathname;
        const isSubdirectory = path.split('/').length > 2; // Check if in subdirectory
        return isSubdirectory ? '../' : './';
    };

    const basePath = getBasePath();
    
    // Load Navbar Component
    const navbarContainer = document.getElementById('navbar-container');
    if (navbarContainer) {
        fetch(`${basePath}components/navbar/navbar.html`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                navbarContainer.innerHTML = html;
                // Call navbar initialization if needed
                if (typeof initializeNavbar === 'function') {
                    initializeNavbar();
                }
            })
            .catch(error => {
                console.error('Error loading navbar:', error);
                // Display error message for debugging
                navbarContainer.innerHTML = `
                    <div style="padding: 20px; background-color: #ff6b6b; color: white; border-radius: 5px;">
                        <h2>Error Loading Navbar Component</h2>
                        <p>${error.message}</p>
                        <p>Path attempted: ${basePath}components/navbar/navbar.html</p>
                    </div>
                `;
            });
    }
    
    // Load Footer Component
    const footerContainer = document.getElementById('footer-container');
    if (footerContainer) {
        fetch(`${basePath}components/footer/footer.html`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                footerContainer.innerHTML = html;
                // Call footer initialization if needed
                if (typeof initializeFooter === 'function') {
                    initializeFooter();
                }
            })
            .catch(error => {
                console.error('Error loading footer:', error);
                // Display error message for debugging
                footerContainer.innerHTML = `
                    <div style="padding: 20px; background-color: #ff6b6b; color: white; border-radius: 5px;">
                        <h2>Error Loading Footer Component</h2>
                        <p>${error.message}</p>
                        <p>Path attempted: ${basePath}components/footer/footer.html</p>
                    </div>
                `;
            });
    }
});
