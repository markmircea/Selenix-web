// Main Components Loader
document.addEventListener('DOMContentLoaded', function() {
    // Function to determine component path based on current location
    function getComponentPath(componentName) {
        // Get path depth to calculate relative path
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        const isRootPath = pathParts.length === 0 || (pathParts.length === 1 && pathParts[0] === 'index.html');
        const isFirstLevel = pathParts.length === 1 || (pathParts.length === 2 && pathParts[1] === 'index.html');
        const isInDocsDir = pathParts.length > 0 && pathParts[0] === 'docs';
        
        console.log('Path parts:', pathParts);
        console.log('Is root path:', isRootPath);
        console.log('Is first level:', isFirstLevel);
        console.log('Is in docs directory:', isInDocsDir);
        
        // Base path varies depending on location in site structure
        let basePath = '';
        if (!isRootPath) {
            if (isInDocsDir) {
                basePath = '../components/'; // For docs/index.html, etc.
            } else if (!isFirstLevel) {
                basePath = '../components/'; // For product/templates.html, etc.
            } else {
                basePath = './components/'; // For other first level pages
            }
        } else {
            basePath = 'components/'; // For root index.html
        }
        
        console.log('Using base path:', basePath);
        console.log('Final component path:', basePath + componentName);
        
        return basePath + componentName;
    }
    
    // Add a debug console log
    console.log('Components.js loaded. Current path:', window.location.pathname);
    
    // Load Navbar Component
    const navbarContainer = document.getElementById('navbar-container');
    if (navbarContainer) {
        const navbarPath = getComponentPath('navbar/navbar.html');
        console.log('Loading navbar from:', navbarPath);
        
        fetch(navbarPath)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                navbarContainer.innerHTML = html;
                console.log('Navbar loaded successfully');
            })
            .catch(error => {
                console.error('Error loading navbar:', error);
                navbarContainer.innerHTML = `
                    <div style="padding: 20px; background-color: #ff6b6b; color: white; border-radius: 5px; margin: 20px 0;">
                        <h2>Error Loading Navbar Component</h2>
                        <p>${error.message}</p>
                        <p>Path attempted: ${navbarPath}</p>
                        <p>Current location: ${window.location.href}</p>
                        <p>Try using the embedded navbar in components/embedded-navbar.html</p>
                    </div>
                `;
            });
    }
    
    // Load Footer Component
    const footerContainer = document.getElementById('footer-container');
    if (footerContainer) {
        const footerPath = getComponentPath('footer/footer.html');
        console.log('Loading footer from:', footerPath);
        
        fetch(footerPath)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                footerContainer.innerHTML = html;
                console.log('Footer loaded successfully');
            })
            .catch(error => {
                console.error('Error loading footer:', error);
                footerContainer.innerHTML = `
                    <div style="padding: 20px; background-color: #ff6b6b; color: white; border-radius: 5px; margin: 20px 0;">
                        <h2>Error Loading Footer Component</h2>
                        <p>${error.message}</p>
                        <p>Path attempted: ${footerPath}</p>
                        <p>Current location: ${window.location.href}</p>
                        <p>Try using the embedded footer in components/embedded-footer.html</p>
                    </div>
                `;
            });
    }
});
