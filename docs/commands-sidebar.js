// Commands Sidebar Management
class CommandsSidebar {
    constructor() {
        this.sidebar = document.getElementById('commands-sidebar');
        this.commandsList = document.getElementById('commands-list');
        this.commandsCount = document.getElementById('commands-count');
        this.searchInput = document.getElementById('commands-search-input');
        this.container = document.querySelector('.docs-container');
        this.categoryOrder = ['scraping', 'data', 'export', 'ai', 'state', 'interaction', 'navigation', 'assertion'];
        this.allCommands = [];
        this.filteredCommands = [];
        this.isVisible = false;
        
        this.init();
    }
    
    init() {
        // Set up search functionality
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.filterCommands(e.target.value);
            });
        }
        
        // Load commands data
        this.loadCommands();
    }
    
    loadCommands() {
        // Load commands from the global ALL_COMMANDS object
        if (typeof ALL_COMMANDS !== 'undefined') {
            this.processCommandsData(ALL_COMMANDS);
        } else {
            // If ALL_COMMANDS is not loaded yet, wait for it
            console.log('Waiting for command data to load...');
            setTimeout(() => this.loadCommands(), 100);
        }
    }
    
    processCommandsData(commandsData) {
        this.allCommands = [];
        
        // Process each category
        Object.entries(commandsData).forEach(([category, commands]) => {
            commands.forEach(command => {
                this.allCommands.push({
                    ...command,
                    category: category,
                    categoryName: this.getCategoryDisplayName(category),
                    badgeClass: this.getBadgeClass(command.badge)
                });
            });
        });
        
        // Sort commands alphabetically
        this.allCommands.sort((a, b) => a.name.localeCompare(b.name));
        this.filteredCommands = [...this.allCommands];
        
        // Update count
        this.updateCommandsCount();
        
        // Render commands
        this.renderCommands();
        
        console.log(`Loaded ${this.allCommands.length} commands`);
    }
    
    getCategoryDisplayName(category) {
        const categoryNames = {
            'scraping': 'Data Extraction',
            'data': 'Data Management',
            'export': 'Import & Export',
            'ai': 'AI & Advanced',
            'state': 'State Management',
            'interaction': 'Interaction',
            'navigation': 'Navigation',
            'assertion': 'Verification'
        };
        return categoryNames[category] || category.charAt(0).toUpperCase() + category.slice(1);
    }
    
    getBadgeClass(badge) {
        if (!badge) return 'basic';
        return badge.toLowerCase().replace(/[^a-z0-9]/g, '-');
    }
    
    filterCommands(searchTerm) {
        if (!searchTerm.trim()) {
            this.filteredCommands = [...this.allCommands];
        } else {
            const term = searchTerm.toLowerCase();
            this.filteredCommands = this.allCommands.filter(command => 
                command.name.toLowerCase().includes(term) ||
                command.description.toLowerCase().includes(term) ||
                command.categoryName.toLowerCase().includes(term)
            );
        }
        
        this.updateCommandsCount();
        this.renderCommands();
    }
    
    updateCommandsCount() {
        if (this.commandsCount) {
            this.commandsCount.textContent = this.filteredCommands.length;
        }
    }
    
    renderCommands() {
        if (!this.commandsList) return;
        
        if (this.filteredCommands.length === 0) {
            this.commandsList.innerHTML = '<li class="no-commands-found">No commands found</li>';
            return;
        }
        
        // Group commands by category for better organization
        const grouped = this.groupCommandsByCategory(this.filteredCommands);
        
        let html = '';
        this.getSortedCategoryEntries(grouped).forEach(([category, commands]) => {
            // Add category divider
            html += `<li class="command-category-divider">${this.getCategoryDisplayName(category)} (${commands.length})</li>`;
            
            // Add commands in this category
            commands.forEach(command => {
                html += this.renderCommandItem(command);
            });
        });

        
        
        this.commandsList.innerHTML = html;
        
        // Add click handlers
        this.addCommandClickHandlers();
    }

    getSortedCategoryEntries(grouped) {
    return this.categoryOrder
        .filter(category => grouped[category] && grouped[category].length > 0)
        .map(category => [category, grouped[category]]);
}
    
    groupCommandsByCategory(commands) {
        return commands.reduce((groups, command) => {
            const category = command.category;
            if (!groups[category]) {
                groups[category] = [];
            }
            groups[category].push(command);
            return groups;
        }, {});
    }
    
    renderCommandItem(command) {
        return `
            <li>
                <a href="#" class="command-link" data-command="${command.name}" data-category="${command.category}">
                    <div class="command-name">${command.name}</div>
                    <div class="command-desc">${command.description}</div>
                    <div class="command-badge-mini ${command.badgeClass}" title="${command.badge}"></div>
                </a>
            </li>
        `;
    }
    
    addCommandClickHandlers() {
        const commandLinks = this.commandsList.querySelectorAll('.command-link');
        commandLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const commandName = e.currentTarget.dataset.command;
                const category = e.currentTarget.dataset.category;
                this.scrollToCommand(commandName, category);
            });
        });
    }
    
    scrollToCommand(commandName, category) {
        console.log(`Scrolling to command: ${commandName} in category: ${category}`);
        
        // Function to perform the actual scrolling
        const performScroll = () => {
            // Try multiple strategies to find the command element
            let commandElement = null;
            
            // Strategy 1: Look for element with exact ID matching command name
            commandElement = document.getElementById(commandName);
            
            if (!commandElement) {
                // Strategy 2: Look for command-item with matching ID
                commandElement = document.querySelector(`.command-item#${commandName}`);
            }
            
            if (!commandElement) {
                // Strategy 3: Look for h3 containing the command name (case insensitive)
                const allCommandItems = document.querySelectorAll('.command-item');
                for (const item of allCommandItems) {
                    const header = item.querySelector('h3 code');
                    if (header && header.textContent.toLowerCase().trim() === commandName.toLowerCase()) {
                        commandElement = item;
                        break;
                    }
                }
            }
            
            if (!commandElement) {
                // Strategy 4: Look for h3 containing the command name as part of text
                const allCommandItems = document.querySelectorAll('.command-item');
                for (const item of allCommandItems) {
                    const header = item.querySelector('h3');
                    if (header && header.textContent.toLowerCase().includes(commandName.toLowerCase())) {
                        commandElement = item;
                        break;
                    }
                }
            }
            
            if (commandElement) {
                console.log(`Found command element for ${commandName}:`, commandElement);
                
                // Scroll to the element
                commandElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'nearest'
                });
                
                // Highlight the command temporarily
                this.highlightCommand(commandElement);
                
                // Also highlight the command in the sidebar
                this.highlightSidebarCommand(commandName);
                
                return true; // Successfully found and scrolled
            } else {
                console.warn(`Could not find command element for: ${commandName}`);
                
                // If we still can't find it, log all available command items for debugging
                const allItems = document.querySelectorAll('.command-item');
                console.log('Available command items:', Array.from(allItems).map(item => {
                    const header = item.querySelector('h3');
                    return {
                        id: item.id,
                        text: header ? header.textContent : 'No header',
                        element: item
                    };
                }));
                
                return false; // Failed to find
            }
        };
        
        // First, make sure the correct category is loaded
        const showCategoryFn = window.showCategory || (typeof showCategory !== 'undefined' ? showCategory : null);
        
        if (showCategoryFn && typeof showCategoryFn === 'function') {
            showCategoryFn(category);
            
            // Wait for content to load, then scroll to command
            // Try multiple times with increasing delays to handle slow loading
            let attempts = 0;
            const maxAttempts = 5;
            
            const tryScroll = () => {
                attempts++;
                const success = performScroll();
                
                if (!success && attempts < maxAttempts) {
                    // Try again with a longer delay
                    setTimeout(tryScroll, 300 + (attempts * 200));
                }
            };
            
            // Start the first attempt after a short delay
            setTimeout(tryScroll, 500);
        } else {
            console.error('showCategory function not available. Available functions:', {
                windowShowCategory: typeof window.showCategory,
                globalShowCategory: typeof showCategory
            });
            
            // If showCategory is not available, try to scroll anyway in case we're already on the right page
            setTimeout(performScroll, 100);
        }
    }
    
    highlightCommand(element) {
        if (!element) return;
        
        // Add highlight class
        element.classList.add('command-highlight');
        
        // Remove highlight after animation
        setTimeout(() => {
            element.classList.remove('command-highlight');
        }, 2000);
    }
    
    show() {
        if (this.sidebar) {
            this.sidebar.style.display = 'block';
            this.isVisible = true;
            
            // Add three-column class to container
            if (this.container) {
                this.container.classList.add('three-column');
            }
        }
    }
    
    hide() {
        if (this.sidebar) {
            this.sidebar.style.display = 'none';
            this.isVisible = false;
            
            // Remove three-column class from container
            if (this.container) {
                this.container.classList.remove('three-column');
            }
        }
    }
    
    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }
    
    // Method to highlight a command in the sidebar (when user clicks on it in main content)
    highlightSidebarCommand(commandName) {
        // Remove existing highlights
        const currentHighlight = this.commandsList.querySelector('.command-link.active');
        if (currentHighlight) {
            currentHighlight.classList.remove('active');
        }
        
        // Add highlight to the clicked command
        const targetLink = this.commandsList.querySelector(`[data-command="${commandName}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
            
            // Scroll the command into view in the sidebar
            targetLink.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }
}

// Initialize the commands sidebar when DOM is loaded
let commandsSidebar;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize after a short delay to ensure all other scripts are loaded
    setTimeout(() => {
        commandsSidebar = new CommandsSidebar();
        
        // Make it globally available
        window.commandsSidebar = commandsSidebar;
    }, 100);
});

// Add CSS for command highlighting
const style = document.createElement('style');
style.textContent = `
    .command-highlight {
        animation: commandHighlight 3s ease-in-out;
        border: 3px solid var(--primary-color, #4f46e5) !important;
        border-radius: 8px !important;
        box-shadow: 0 0 20px rgba(79, 70, 229, 0.3) !important;
        position: relative;
        z-index: 10;
    }
    
    @keyframes commandHighlight {
        0% {
            background-color: rgba(79, 70, 229, 0.15);
            transform: scale(1.03);
            box-shadow: 0 0 20px rgba(79, 70, 229, 0.4);
        }
        25% {
            background-color: rgba(79, 70, 229, 0.25);
            transform: scale(1.03);
            box-shadow: 0 0 30px rgba(79, 70, 229, 0.5);
        }
        50% {
            background-color: rgba(79, 70, 229, 0.2);
            transform: scale(1.02);
            box-shadow: 0 0 25px rgba(79, 70, 229, 0.4);
        }
        75% {
            background-color: rgba(79, 70, 229, 0.1);
            transform: scale(1.01);
            box-shadow: 0 0 15px rgba(79, 70, 229, 0.3);
        }
        100% {
            background-color: transparent;
            transform: scale(1);
            box-shadow: none;
        }
    }
    
    .command-link.active {
        background-color: rgba(79, 70, 229, 0.15) !important;
        color: var(--primary-color) !important;
        font-weight: 600 !important;
        border-left: 4px solid var(--primary-color, #4f46e5) !important;
    }
    
    /* Ensure command items are scrollable targets */
    .command-item {
        scroll-margin-top: 100px;
        transition: all 0.3s ease;
    }
    
    /* Add a subtle pulse effect when a command is being targeted */
    .command-item:target {
        background-color: rgba(79, 70, 229, 0.05);
        border-left: 4px solid var(--primary-color, #4f46e5);
        padding-left: calc(2rem - 4px);
    }
`;
document.head.appendChild(style);