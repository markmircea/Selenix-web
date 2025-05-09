// Documentation-specific JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle search functionality
    const searchInput = document.querySelector('.search-input');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                // If search is empty, show all links
                sidebarLinks.forEach(link => {
                    link.parentElement.style.display = 'block';
                });
                return;
            }
            
            // Filter links based on search term
            sidebarLinks.forEach(link => {
                const text = link.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    link.parentElement.style.display = 'block';
                    // Highlight matching text (optional)
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    const originalText = link.innerHTML;
                    
                    // Only highlight the text part, not the icon
                    const iconHTML = link.querySelector('i')?.outerHTML || '';
                    const textContent = link.textContent;
                    
                    link.innerHTML = iconHTML + ' ' + textContent.replace(regex, '<span class="highlight">$1</span>');
                    
                    // Add event to restore original text when search changes
                    searchInput.addEventListener('change', function() {
                        link.innerHTML = originalText;
                    }, { once: true });
                } else {
                    link.parentElement.style.display = 'none';
                }
            });
        });
    }
    
    // Active link tracking based on scroll position
    const sections = document.querySelectorAll('.docs-section');
    const navLinks = document.querySelectorAll('.sidebar-nav a[href^="#"]');
    
    function setActiveLink() {
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (window.scrollY >= sectionTop - 100 && window.scrollY < sectionTop + sectionHeight - 100) {
                currentSection = '#' + section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentSection) {
                link.classList.add('active');
            }
        });
    }
    
    // Set active link on initial load
    window.addEventListener('load', setActiveLink);
    // Update active link on scroll
    window.addEventListener('scroll', setActiveLink);
    
    // Smooth scrolling for documentation links
    document.querySelectorAll('.docs-content a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100, // Offset for header
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Handle mobile sidebar toggle (for responsive design)
    const mobileSidebarToggle = document.querySelector('.mobile-sidebar-toggle');
    const docsSidebar = document.querySelector('.docs-sidebar');
    
    if (mobileSidebarToggle && docsSidebar) {
        mobileSidebarToggle.addEventListener('click', function() {
            docsSidebar.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
    
    // Handle dropdown menu for product submenu
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        // For touch devices, handle click instead of hover
        if ('ontouchstart' in window) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.dropdown');
                parent.classList.toggle('active');
                
                // Close other open dropdowns
                dropdownToggles.forEach(otherToggle => {
                    if (otherToggle !== toggle) {
                        otherToggle.closest('.dropdown').classList.remove('active');
                    }
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdownToggles.forEach(toggle => {
                        toggle.closest('.dropdown').classList.remove('active');
                    });
                }
            });
        }
    });
});