// Component error handler for documentation pages
window.addEventListener('load', function() {
    console.log('Component error handler loaded');
    
    setTimeout(function() {
        // Check if navbar was loaded properly
        const navbarContainer = document.getElementById('navbar-container');
        const footerContainer = document.getElementById('footer-container');
        
        // Function to manually load a component
        function manuallyLoadComponent(component, containerId) {
            console.log(`Manually loading ${component} component`);
            
            // Attempt to fetch the component
            fetch(`../components/${component}/${component}.html`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    document.getElementById(containerId).innerHTML = html;
                    console.log(`${component} component manually loaded successfully`);
                    
                    // Initialize navbar if it's the navbar component
                    if (component === 'navbar' && window.initializeNavbar) {
                        window.initializeNavbar();
                    }
                })
                .catch(error => {
                    console.error(`Error manually loading ${component}:`, error);
                    document.getElementById(containerId).classList.add('component-error');
                });
        }
        
        // Check if navbar failed to load (contains error div or is empty)
        if (navbarContainer) {
            const hasNavbarError = navbarContainer.querySelector('.component-placeholder') || 
                                   navbarContainer.innerHTML.trim() === '' ||
                                   navbarContainer.querySelector('[style*="background-color: #ff6b6b"]');
                                   
            if (hasNavbarError) {
                console.log('Navbar failed to load, attempting manual load');
                manuallyLoadComponent('navbar', 'navbar-container');
            }
        }
        
        // Check if footer failed to load
        if (footerContainer) {
            const hasFooterError = footerContainer.querySelector('.component-placeholder') || 
                                   footerContainer.innerHTML.trim() === '' ||
                                   footerContainer.querySelector('[style*="background-color: #ff6b6b"]');
                                   
            if (hasFooterError) {
                console.log('Footer failed to load, attempting manual load');
                manuallyLoadComponent('footer', 'footer-container');
            }
        }
    }, 1000); // Wait 1 second to check components
});
