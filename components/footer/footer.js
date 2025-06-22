// Footer Component JavaScript
function initializeFooter() {
    // Update copyright year dynamically
    const yearSpan = document.querySelector('.footer-copyright-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
    
    // Fix navigation paths based on the current location
    const pathParts = window.location.pathname.split('/').filter(Boolean);
    const isRootPath = pathParts.length === 0 || (pathParts.length === 1 && pathParts[0] === 'index.html');
    const isFirstLevel = pathParts.length === 1 || (pathParts.length === 2 && pathParts[1] === 'index.html');
    let prefix = '';
    
    // Add proper prefix based on path depth
    if (!isRootPath) {
        if (!isFirstLevel) {
            prefix = '../';  // For deeper paths like product/templates
        } else {
            prefix = './';   // For first level paths like docs/ or product/
        }
    }
    
    // Update footer links
    const footerLinks = {
        'footer-docs': prefix + 'docs/index.html',
        'footer-no-code-builder': prefix + 'product/no-code-builder.html',
        'footer-no-code-steps': prefix + 'product/no-code-steps.html',
        'footer-templates': prefix + 'product/templates.html'
    };
    
    // Update each link href
    Object.keys(footerLinks).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.href = footerLinks[id];
        }
    });
    
    console.log('Footer initialized successfully with dynamic year:', new Date().getFullYear());
}
