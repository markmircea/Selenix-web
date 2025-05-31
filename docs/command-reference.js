// Command Reference specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Only run this on the command reference page
    if (window.location.hash.includes('command-reference') || document.querySelector('.command-categories')) {
        initializeCommandReference();
    }
});

function initializeCommandReference() {
    console.log('Initializing Command Reference page...');
    
    // Initialize category switching
    setupCategoryNavigation();
    
    // Initialize search functionality
    setupCommandSearch();
    
    // Initialize filter buttons
    setupCommandFilters();
    
    // Load command sections
    loadCommandSections();
    
    // Show default category or handle URL hash
    handleInitialView();
}

function setupCategoryNavigation() {
    // Make category cards clickable
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            const category = this.getAttribute('onclick')?.match(/showCategory\('(.+)'\)/)?.[1];
            if (category) {
                showCategory(category);
            }
        });
        
        // Make cards look clickable
        card.style.cursor = 'pointer';
        card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 8px 25px rgba(79, 70, 229, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
        });
    });
}

function setupCommandSearch() {
    const searchInput = document.getElementById('commandSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterCommandsBySearch(this.value);
        });
    }
}

function setupCommandFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
        });
    });
}

async function loadCommandSections() {
    console.log('Loading command sections...');
    
    const sections = {
        'interaction': 'interaction-commands.html',
        'scraping': 'scraping-commands.html', 
        'assertion': 'assertion-commands-complete.html',
        'navigation': 'navigation-commands.html',
        'data': 'data-commands.html',
        'export': 'export-commands.html',
        'ai': 'ai-commands.html',
        'state': 'state-commands.html'
    };
    
    // Create containers for each section if they don't exist
    Object.keys(sections).forEach(category => {
        let container = document.getElementById(`${category}-section`);
        if (!container) {
            container = document.createElement('div');
            container.id = `${category}-section`;
            container.className = 'command-section';
            container.style.display = 'none';
            
            // Insert after the command categories
            const insertAfter = document.querySelector('.command-categories').parentElement;
            insertAfter.appendChild(container);
        }
    });
    
    // Load content for each section
    for (const [category, filename] of Object.entries(sections)) {
        try {
            const response = await fetch(`./content/${filename}`);
            if (response.ok) {
                const content = await response.text();
                const container = document.getElementById(`${category}-section`);
                if (container) {
                    container.innerHTML = content;
                    console.log(`Loaded ${category} commands`);
                }
            } else {
                console.warn(`Could not load ${filename} - Status: ${response.status}`);
                // Create placeholder content
                createPlaceholderContent(category);
            }
        } catch (error) {
            console.warn(`Error loading ${filename}:`, error);
            createPlaceholderContent(category);
        }
    }
}

function createPlaceholderContent(category) {
    const container = document.getElementById(`${category}-section`);
    if (container) {
        container.innerHTML = `
            <div class="command-section-placeholder">
                <h2><i class="fa-solid fa-construction"></i> ${category.charAt(0).toUpperCase() + category.slice(1)} Commands</h2>
                <p>This section is being developed. Check back soon for detailed command documentation!</p>
                <div class="alert alert-info">
                    <i class="fa-solid fa-info-circle"></i>
                    <div>
                        <strong>Coming Soon</strong>
                        <p>We're working on comprehensive documentation for ${category} commands. In the meantime, you can use the AI assistant for help with these commands.</p>
                    </div>
                </div>
            </div>
        `;
    }
}

function showCategory(category) {
    console.log(`Showing category: ${category}`);
    
    // Hide all sections
    document.querySelectorAll('.command-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Hide the main categories view
    const categoriesSection = document.querySelector('.command-categories')?.parentElement;
    if (categoriesSection) {
        categoriesSection.style.display = 'none';
    }
    
    // Hide the search section temporarily
    const searchSection = document.querySelector('.command-search')?.parentElement;
    if (searchSection) {
        searchSection.style.display = 'none';
    }
    
    // Show the selected category section
    const targetSection = document.getElementById(`${category}-section`);
    if (targetSection) {
        targetSection.style.display = 'block';
        
        // Add a back button if it doesn't exist
        if (!targetSection.querySelector('.back-to-categories')) {
            const backButton = document.createElement('div');
            backButton.className = 'back-to-categories';
            backButton.innerHTML = `
                <button onclick="showAllCategories()" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Back to All Categories
                </button>
            `;
            targetSection.insertBefore(backButton, targetSection.firstChild);
        }
        
        // Scroll to top
        window.scrollTo(0, 0);
        
        // Update URL hash
        window.location.hash = `command-reference-${category}`;
    } else {
        console.error(`Section not found: ${category}-section`);
    }
}

function showAllCategories() {
    console.log('Showing all categories');
    
    // Hide all command sections
    document.querySelectorAll('.command-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Show the main categories view
    const categoriesSection = document.querySelector('.command-categories')?.parentElement;
    if (categoriesSection) {
        categoriesSection.style.display = 'block';
    }
    
    // Show the search section
    const searchSection = document.querySelector('.command-search')?.parentElement;
    if (searchSection) {
        searchSection.style.display = 'block';
    }
    
    // Update URL hash
    window.location.hash = 'command-reference';
    
    // Scroll to top
    window.scrollTo(0, 0);
}

function filterCommands(type) {
    console.log(`Filtering commands by: ${type}`);
    // Implementation for filtering commands by type
    // This would filter visible commands based on their complexity level
}

function filterCommandsBySearch(query) {
    console.log(`Searching commands for: ${query}`);
    // Implementation for searching through commands
    // This would show/hide commands based on search terms
}

function handleInitialView() {
    // Check if there's a specific category in the URL hash
    const hash = window.location.hash;
    
    if (hash.includes('command-reference-')) {
        const category = hash.replace('#command-reference-', '');
        setTimeout(() => showCategory(category), 100);
    } else {
        // Show all categories by default
        showAllCategories();
    }
}

// Global functions (can be called from onclick attributes)
window.showCategory = showCategory;
window.showAllCategories = showAllCategories;
window.filterCommands = filterCommands;