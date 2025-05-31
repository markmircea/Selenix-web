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
    
    // Get the content container
    const contentContainer = document.getElementById('command-content-container');
    if (!contentContainer) {
        console.error('Command content container not found');
        return;
    }
    
    // Show loading state
    contentContainer.innerHTML = `
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Loading ${category} commands...</p>
        </div>
    `;
    
    // Hide the main categories view
    const categoriesSection = document.getElementById('main-categories');
    if (categoriesSection) {
        categoriesSection.style.display = 'none';
    }
    
    // Hide the search section temporarily
    const searchSection = document.getElementById('command-search-section');
    if (searchSection) {
        searchSection.style.display = 'none';
    }
    
    // Hide the command basics section
    const commandBasics = document.getElementById('command-basics');
    if (commandBasics) {
        commandBasics.style.display = 'none';
    }
    
    // Hide the element selectors section
    const elementSelectors = document.getElementById('element-selectors');
    if (elementSelectors) {
        elementSelectors.style.display = 'none';
    }
    
    // Hide the variables section
    const variablesSection = document.getElementById('variables-section');
    if (variablesSection) {
        variablesSection.style.display = 'none';
    }
    
    // Determine the file path based on the category
    let filePath = '';
    
    switch(category) {
        case 'interaction':
            filePath = './content/interaction-commands.html';
            break;
        case 'scraping':
            filePath = './content/scraping-commands.html';
            break;
        case 'assertion':
            filePath = './content/assertion-commands-complete.html';
            break;
        case 'data':
            filePath = './content/data-commands.html';
            break;
        case 'export':
            filePath = './content/export-commands.html';
            break;
        case 'ai':
            filePath = './content/ai-commands.html';
            break;
        case 'state':
            filePath = './content/state-commands.html';
            break;
        case 'navigation':
            filePath = './content/navigation-commands.html';
            break;
        default:
            filePath = `./content/${category}-commands.html`;
    }
    
    console.log(`Loading content from: ${filePath}`);
    
    // Load the content from the corresponding HTML file
    fetch(filePath)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load ${category} commands (Status: ${response.status})`);
            }
            return response.text();
        })
        .then(html => {
            // Display the content with a back button
            contentContainer.innerHTML = `
                <div class="back-to-categories">
                    <button onclick="showAllCategories()" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i> Back to All Categories
                    </button>
                </div>
                <div class="command-section" style="display: block;">
                    ${html}
                </div>
            `;
            
            // Update URL hash - but keep the main command-reference hash to avoid conflicts
            if (window.location.hash !== '#command-reference') {
                window.location.hash = 'command-reference';
            }
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Log success
            console.log(`Successfully loaded ${category} commands`);
        })
        .catch(error => {
            console.error(`Error loading ${category} commands:`, error);
            contentContainer.innerHTML = `
                <div class="error-message">
                    <h1>Error Loading Commands</h1>
                    <p>${error.message}</p>
                    <p>Could not load content for ${category} commands.</p>
                    <button onclick="showAllCategories()" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i> Back to Categories
                    </button>
                </div>
            `;
        });
}

function showAllCategories() {
    console.log('Showing all categories');
    
    // Clear the content container
    const contentContainer = document.getElementById('command-content-container');
    if (contentContainer) {
        contentContainer.innerHTML = '';
    }
    
    // Show the main categories view
    const categoriesSection = document.getElementById('main-categories');
    if (categoriesSection) {
        categoriesSection.style.display = 'block';
    }
    
    // Show the search section
    const searchSection = document.getElementById('command-search-section');
    if (searchSection) {
        searchSection.style.display = 'block';
    }
    
    // Show the command basics section
    const commandBasics = document.getElementById('command-basics');
    if (commandBasics) {
        commandBasics.style.display = 'block';
    }
    
    // Show the element selectors section
    const elementSelectors = document.getElementById('element-selectors');
    if (elementSelectors) {
        elementSelectors.style.display = 'block';
    }
    
    // Show the variables section
    const variablesSection = document.getElementById('variables-section');
    if (variablesSection) {
        variablesSection.style.display = 'block';
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
    
    if (!query.trim()) {
        // If search is empty, clear results
        return;
    }
    
    // Show loading state in the content container
    const contentContainer = document.getElementById('command-content-container');
    if (contentContainer) {
        contentContainer.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Searching for "${query}" across all commands...</p>
            </div>
        `;
    }
    
    // Perform the search across all command categories
    searchAllCommands(query)
        .then(results => {
            displaySearchResults(results, query);
        })
        .catch(error => {
            console.error('Error searching commands:', error);
            if (contentContainer) {
                contentContainer.innerHTML = `
                    <div class="error-message">
                        <h1>Search Error</h1>
                        <p>${error.message}</p>
                        <button onclick="showAllCategories()" class="back-btn">
                            <i class="fa-solid fa-arrow-left"></i> Back to Categories
                        </button>
                    </div>
                `;
            }
        });
}

async function searchAllCommands(query) {
    if (!query.trim()) return [];
    
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
    
    const results = [];
    
    // Load all command content if not already loaded
    for (const [category, filename] of Object.entries(sections)) {
        try {
            const response = await fetch(`./content/${filename}`);
            if (response.ok) {
                const content = await response.text();
                
                // Create a temporary element to parse the HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;
                
                // Find all command items
                const commandItems = tempDiv.querySelectorAll('.command-item');
                
                // Check each command for the search term
                commandItems.forEach(item => {
                    const commandName = item.querySelector('.command-header h3')?.textContent || '';
                    const commandDesc = item.querySelector('.command-description')?.textContent || '';
                    const commandText = item.textContent;
                    
                    if (
                        commandName.toLowerCase().includes(query.toLowerCase()) ||
                        commandDesc.toLowerCase().includes(query.toLowerCase()) ||
                        commandText.toLowerCase().includes(query.toLowerCase())
                    ) {
                        results.push({
                            name: commandName,
                            description: commandDesc,
                            category: category,
                            html: item.outerHTML,
                            id: item.id || `command-${Math.random().toString(36).substr(2, 9)}`
                        });
                    }
                });
            }
        } catch (error) {
            console.warn(`Error searching ${filename}:`, error);
        }
    }
    
    return results;
}

function displaySearchResults(results, query) {
    const contentContainer = document.getElementById('command-content-container');
    if (!contentContainer) return;
    
    // Hide the main categories view
    const categoriesSection = document.getElementById('main-categories');
    if (categoriesSection) {
        categoriesSection.style.display = 'none';
    }
    
    // Hide the search section temporarily
    const searchSection = document.getElementById('command-search-section');
    if (searchSection) {
        searchSection.style.display = 'none';
    }
    
    // Hide the command basics section
    const commandBasics = document.getElementById('command-basics');
    if (commandBasics) {
        commandBasics.style.display = 'none';
    }
    
    // Hide the element selectors section
    const elementSelectors = document.getElementById('element-selectors');
    if (elementSelectors) {
        elementSelectors.style.display = 'none';
    }
    
    // Hide the variables section
    const variablesSection = document.getElementById('variables-section');
    if (variablesSection) {
        variablesSection.style.display = 'none';
    }
    
    if (results.length === 0) {
        contentContainer.innerHTML = `
            <div class="back-to-categories">
                <button onclick="showAllCategories()" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Back to All Categories
                </button>
            </div>
            <div class="search-results-empty">
                <h2><i class="fa-solid fa-search"></i> Search Results</h2>
                <p>No commands found matching "${query}"</p>
            </div>
        `;
        return;
    }
    
    // Group results by category
    const groupedResults = {};
    results.forEach(result => {
        if (!groupedResults[result.category]) {
            groupedResults[result.category] = [];
        }
        groupedResults[result.category].push(result);
    });
    
    // Build the results HTML
    let resultsHTML = `
        <div class="back-to-categories">
            <button onclick="showAllCategories()" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back to All Categories
            </button>
        </div>
        <div class="search-results-header">
            <h2><i class="fa-solid fa-search"></i> Search Results</h2>
            <p>Found ${results.length} commands matching "${query}"</p>
        </div>
    `;
    
    // Add results by category
    for (const [category, categoryResults] of Object.entries(groupedResults)) {
        const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
        
        resultsHTML += `
            <div class="search-results-category">
                <h3><i class="fa-solid fa-folder"></i> ${categoryName} Commands (${categoryResults.length})</h3>
                <div class="command-list">
        `;
        
        categoryResults.forEach(result => {
            resultsHTML += result.html;
        });
        
        resultsHTML += `
                </div>
            </div>
        `;
    }
    
    contentContainer.innerHTML = resultsHTML;
    
    // Update URL hash - but keep the main command-reference hash to avoid conflicts
    if (window.location.hash !== '#command-reference') {
        window.location.hash = 'command-reference';
    }
}

function handleInitialView() {
    // Check if we're on the command reference page
    const hash = window.location.hash;
    
    if (hash === '#command-reference') {
        // Show all categories by default
        showAllCategories();
    } else {
        // If we're not on the command reference page, don't do anything
        // The docs-renderer.js will handle loading the appropriate page
    }
}

// Global functions (can be called from onclick attributes)
window.showCategory = showCategory;
window.showAllCategories = showAllCategories;
window.filterCommands = filterCommands;
