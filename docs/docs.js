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
    
    // Image Modal Functionality
    initImageModal();
    
    // All alert and common styling is now centralized in docs.css
});

// Image Modal Functions
function initImageModal() {
    // Create modal if it doesn't exist
    let modal = document.querySelector('.image-modal');
    if (!modal) {
        modal = createImageModal();
    }
    
    setupImageClickHandlers();
}

function createImageModal() {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <span class="image-modal-close">&times;</span>
        <img class="image-modal-content" alt="">
        <div class="image-modal-caption"></div>
    `;
    document.body.appendChild(modal);
    
    // Add modal styles
    addImageModalStyles();
    
    // Setup modal event listeners
    setupModalEventListeners(modal);
    
    return modal;
}

function addImageModalStyles() {
    if (document.querySelector('#image-modal-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'image-modal-styles';
    style.textContent = `
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            animation: fadeIn 0.3s ease;
        }
        
        .image-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-modal-content {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            animation: zoomIn 0.3s ease;
        }
        
        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
            z-index: 10000;
        }
        
        .image-modal-close:hover {
            color: #ccc;
        }
        
        .image-modal-caption {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            text-align: center;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 6px;
            max-width: 80%;
        }
        
        .screenshot img {
            cursor: pointer;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.7); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}

function setupModalEventListeners(modal) {
    const modalImg = modal.querySelector('.image-modal-content');
    const caption = modal.querySelector('.image-modal-caption');
    const closeBtn = modal.querySelector('.image-modal-close');
    
    // Close modal functionality
    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Close on X button click
    closeBtn.addEventListener('click', closeModal);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
    
    // Store references for later use
    modal._modalImg = modalImg;
    modal._caption = caption;
    modal._closeModal = closeModal;
}

function setupImageClickHandlers() {
    // Use event delegation to handle dynamically loaded images
    document.removeEventListener('click', handleImageClick);
    document.addEventListener('click', handleImageClick);
}

function handleImageClick(e) {
    // Check if clicked element is a screenshot image
    if (e.target.matches('.screenshot img')) {
        const modal = document.querySelector('.image-modal');
        if (!modal) return;
        
        const modalImg = modal._modalImg;
        const caption = modal._caption;
        
        modal.classList.add('show');
        modalImg.src = e.target.src;
        modalImg.alt = e.target.alt;
        caption.textContent = e.target.alt;
        document.body.style.overflow = 'hidden';
    }
}
