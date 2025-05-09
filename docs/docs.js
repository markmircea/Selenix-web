// Documentation page specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality for documentation
    const searchInput = document.querySelector('.search-input');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    
    if (searchInput && sidebarLinks.length > 0) {
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
                const linkText = link.textContent.toLowerCase();
                if (linkText.includes(searchTerm)) {
                    link.parentElement.style.display = 'block';
                } else {
                    link.parentElement.style.display = 'none';
                }
            });
        });
    }
    
    // Add anchor links to headings
    document.querySelectorAll('.docs-content h2, .docs-content h3').forEach(heading => {
        // Only add if it doesn't already have an ID
        if (!heading.id) {
            // Generate ID from heading text
            const id = heading.textContent
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            heading.id = id;
        }
        
        // Create anchor link
        const anchor = document.createElement('a');
        anchor.href = `#${heading.id}`;
        anchor.className = 'heading-anchor';
        anchor.innerHTML = '<i class="fa-solid fa-link"></i>';
        anchor.title = 'Link to this section';
        
        // Add anchor to heading
        heading.appendChild(anchor);
    });
    
    // Highlight current section in sidebar based on scroll position
    function highlightCurrentSection() {
        const scrollPosition = window.scrollY;
        
        // Get all section headings
        const headings = document.querySelectorAll('.docs-content h2');
        
        // Find the current section based on scroll position
        let currentSection = null;
        headings.forEach(heading => {
            if (heading.offsetTop - 100 <= scrollPosition) {
                currentSection = heading.id;
            }
        });
        
        // If we found a current section, highlight it in the sidebar
        if (currentSection) {
            sidebarLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href.includes(currentSection)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }
    }
    
    // Run highlight on scroll
    window.addEventListener('scroll', highlightCurrentSection);
    
    // Initial highlight
    highlightCurrentSection();
    
    // Add copy buttons to code blocks
    document.querySelectorAll('pre code').forEach(codeBlock => {
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-button';
        copyButton.innerHTML = '<i class="fa-solid fa-copy"></i>';
        copyButton.title = 'Copy to clipboard';
        
        // Position the button
        copyButton.style.position = 'absolute';
        copyButton.style.top = '0.5rem';
        copyButton.style.right = '0.5rem';
        
        // Style the container for positioning
        const pre = codeBlock.parentElement;
        pre.style.position = 'relative';
        
        // Add copy functionality
        copyButton.addEventListener('click', function() {
            const code = codeBlock.textContent;
            navigator.clipboard.writeText(code).then(() => {
                // Show copied status
                copyButton.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => {
                    copyButton.innerHTML = '<i class="fa-solid fa-copy"></i>';
                }, 2000);
            });
        });
        
        pre.appendChild(copyButton);
    });
    
    // Add alert styling
    const alertStyles = `
        .alert {
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .alert i {
            font-size: 1.5rem;
            margin-top: 0.25rem;
        }
        
        .alert-info {
            background-color: rgba(79, 70, 229, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .alert-info i {
            color: var(--primary-color);
        }
        
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            border-left: 4px solid #f59e0b;
        }
        
        .alert-warning i {
            color: #f59e0b;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid #10b981;
        }
        
        .alert-success i {
            color: #10b981;
        }
        
        .image-container {
            margin: 2rem 0;
            text-align: center;
        }
        
        .docs-image {
            max-width: 100%;
            border-radius: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }
        
        .image-caption {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: #6b7280;
            font-style: italic;
        }
        
        .docs-list {
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .docs-list li {
            margin-bottom: 0.75rem;
        }
        
        .heading-anchor {
            opacity: 0;
            margin-left: 0.5rem;
            font-size: 0.9rem;
            color: var(--border-color);
            text-decoration: none;
            transition: opacity 0.2s ease, color 0.2s ease;
        }
        
        h2:hover .heading-anchor,
        h3:hover .heading-anchor {
            opacity: 1;
            color: var(--primary-color);
        }
    `;
    
    // Add styles to head
    const style = document.createElement('style');
    style.textContent = alertStyles;
    document.head.appendChild(style);
});
