// docs-renderer.js - Main documentation renderer
class DocsRenderer {
    constructor(config) {
        this.config = config;
        this.currentPage = null;
        this.contentCache = new Map();
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.renderSidebar();
        this.loadInitialPage();
    }

    setupEventListeners() {
        // Handle URL changes (back/forward buttons)
        window.addEventListener('popstate', (event) => {
            const pageId = this.getPageFromURL();
            this.loadPage(pageId, false);
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }
    }

    renderSidebar() {
        const sidebar = document.querySelector('.docs-sidebar');
        if (!sidebar) return;

        let sidebarHTML = `
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search documentation...">
                <i class="fa-solid fa-search search-icon"></i>
            </div>
        `;

        this.config.sections.forEach(section => {
            sidebarHTML += `
                <div class="sidebar-section">
                    <h3><i class="${section.icon}"></i> ${section.title}</h3>
                    <ul class="sidebar-nav">
            `;

            section.pages.forEach(page => {
                sidebarHTML += `
                    <li>
                        <a href="#${page.id}" data-page="${page.id}" class="nav-link">
                            <i class="${page.icon}"></i> ${page.title}
                        </a>
                    </li>
                `;
            });

            sidebarHTML += `
                    </ul>
                </div>
            `;
        });

        // Update sidebar
        sidebar.innerHTML = sidebarHTML;
        
        // Add click listeners to navigation links
        sidebar.addEventListener('click', (e) => {
            if (e.target.classList.contains('nav-link') || e.target.closest('.nav-link')) {
                e.preventDefault();
                const link = e.target.classList.contains('nav-link') ? e.target : e.target.closest('.nav-link');
                const pageId = link.dataset.page;
                this.loadPage(pageId, true);
            }
        });
    }

    async loadPage(pageId, updateURL = true) {
        const page = this.findPageById(pageId);
        if (!page) {
            console.error(`Page not found: ${pageId}`);
            return;
        }

        try {
            // Show loading state
            const contentContainer = document.querySelector('.docs-content');
            if (contentContainer) {
                contentContainer.classList.add('loading');
            }

            // Update URL if requested
            if (updateURL) {
                history.pushState({ pageId }, '', `#${pageId}`);
            }

            // Update active navigation
            this.updateActiveNav(pageId);
            
            // Handle commands sidebar visibility
            if (window.commandsSidebar) {
                if (pageId === 'command-reference') {
                    window.commandsSidebar.show();
                } else {
                    window.commandsSidebar.hide();
                }
            }

            // Load and render content
            const content = await this.loadHtmlContent(page.file);
            
            // Update page content
            if (contentContainer) {
                contentContainer.innerHTML = content;
                contentContainer.classList.remove('loading');
                this.enhanceContent();
            }

            this.currentPage = pageId;

        } catch (error) {
            console.error('Error loading page:', error);
            this.showError('Failed to load page content');
        }
    }

    async loadHtmlContent(filename) {
        // Check cache first
        if (this.contentCache.has(filename)) {
            return this.contentCache.get(filename);
        }

        try {
            const fullPath = `${this.config.contentPath}${filename}`;
            console.log('Loading content from:', fullPath);
            
            const response = await fetch(fullPath);
            if (!response.ok) {
                throw new Error(`Failed to load ${filename} - Status: ${response.status}`);
            }
            
            const content = await response.text();
            
            // Extract only the content, removing any full HTML structure
            const cleanedContent = this.extractContentFromHtml(content);
            
            this.contentCache.set(filename, cleanedContent);
            return cleanedContent;
        } catch (error) {
            console.error('Error loading HTML content:', error);
            return this.getErrorContent(filename);
        }
    }

    extractContentFromHtml(htmlString) {
        // Create a temporary DOM element to parse the HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlString;
        
        // Try to find content in common containers
        let contentElement = 
            tempDiv.querySelector('.docs-content') ||
            tempDiv.querySelector('.content') ||
            tempDiv.querySelector('main') ||
            tempDiv.querySelector('body') ||
            tempDiv;
        
        // If we found a specific content container, use its innerHTML
        if (contentElement && contentElement !== tempDiv) {
            return contentElement.innerHTML;
        }
        
        // Otherwise, remove script tags and return the cleaned HTML
        const scripts = tempDiv.querySelectorAll('script');
        scripts.forEach(script => script.remove());
        
        // Remove head, html, body tags but keep the content
        const body = tempDiv.querySelector('body');
        if (body) {
            return body.innerHTML;
        }
        
        return tempDiv.innerHTML;
    }

    enhanceContent() {
        // Handle section scrolling from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const scrollTo = urlParams.get('scrollTo');
        
        if (scrollTo) {
            // Scroll to specific section after content loads
            setTimeout(() => {
                const element = document.getElementById(scrollTo);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Highlight the section briefly
                    element.style.backgroundColor = '#fff3cd';
                    element.style.transition = 'background-color 0.3s ease';
                    setTimeout(() => {
                        element.style.backgroundColor = '';
                    }, 2000);
                } else {
                    console.warn(`Section with ID "${scrollTo}" not found`);
                }
            }, 100);
        } else {
            // Add scroll-to-top on page change only if no specific section requested
            window.scrollTo(0, 0);
        }
        
        // Enhance code blocks with copy functionality
        const codeBlocks = document.querySelectorAll('pre code');
        codeBlocks.forEach(block => {
            // Only add copy button if it doesn't already exist
            if (!block.parentNode.parentNode.querySelector('.copy-code-btn')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'code-block-wrapper';
                block.parentNode.parentNode.insertBefore(wrapper, block.parentNode);
                wrapper.appendChild(block.parentNode);
                
                const copyBtn = document.createElement('button');
                copyBtn.className = 'copy-code-btn';
                copyBtn.innerHTML = '<i class="fa-solid fa-copy"></i>';
                copyBtn.addEventListener('click', () => {
                    navigator.clipboard.writeText(block.textContent);
                    copyBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="fa-solid fa-copy"></i>';
                    }, 2000);
                });
                wrapper.appendChild(copyBtn);
            }
        });

        // Re-initialize any JavaScript components that might be in the loaded content
        this.initializeLoadedContent();
    }

    initializeLoadedContent() {
        // Initialize any interactive elements in the loaded content
        // This is where you can add handlers for custom components
        
        // Example: Initialize tooltips, modals, or other interactive elements
        const interactiveElements = document.querySelectorAll('[data-toggle]');
        interactiveElements.forEach(element => {
            // Add your custom initialization logic here
        });
        
        // Initialize any syntax highlighting if you're using it
        if (window.Prism) {
            window.Prism.highlightAll();
        }
        
        // Initialize any other JavaScript components your HTML pages might contain
    }

    updateActiveNav(pageId) {
        // Remove all active classes
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Add active class to current page
        const activeLink = document.querySelector(`[data-page="${pageId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    findPageById(pageId) {
        for (const section of this.config.sections) {
            const page = section.pages.find(p => p.id === pageId);
            if (page) return page;
        }
        return null;
    }

    getPageFromURL() {
        const hash = window.location.hash.slice(1);
        return hash || this.config.defaultPage;
    }

    loadInitialPage() {
        const pageId = this.getPageFromURL();
        this.loadPage(pageId, false);
    }

    handleSearch(query) {
        if (!query.trim()) {
            this.clearSearchResults();
            return;
        }

        // Simple search implementation
        const results = [];
        this.config.sections.forEach(section => {
            section.pages.forEach(page => {
                if (page.title.toLowerCase().includes(query.toLowerCase())) {
                    results.push({ ...page, section: section.title });
                }
            });
        });

        this.showSearchResults(results, query);
    }

    showSearchResults(results, query) {
        const sidebar = document.querySelector('.docs-sidebar');
        if (!sidebar) return;

        let searchHTML = `
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search documentation..." value="${query}">
                <i class="fa-solid fa-search search-icon"></i>
            </div>
            <div class="search-results">
                <h3>Search Results (${results.length})</h3>
        `;

        if (results.length === 0) {
            searchHTML += '<p class="no-results">No results found</p>';
        } else {
            searchHTML += '<ul class="search-results-list">';
            results.forEach(result => {
                searchHTML += `
                    <li>
                        <a href="#${result.id}" data-page="${result.id}" class="nav-link search-result">
                            <i class="${result.icon}"></i>
                            <div>
                                <div class="result-title">${result.title}</div>
                                <div class="result-section">${result.section}</div>
                            </div>
                        </a>
                    </li>
                `;
            });
            searchHTML += '</ul>';
        }

        searchHTML += `
                <button class="clear-search-btn">
                    <i class="fa-solid fa-times"></i> Clear Search
                </button>
            </div>
        `;

        sidebar.innerHTML = searchHTML;

        // Re-add event listeners
        sidebar.querySelector('.search-input').addEventListener('input', (e) => {
            this.handleSearch(e.target.value);
        });

        sidebar.querySelector('.clear-search-btn').addEventListener('click', () => {
            this.clearSearchResults();
        });
    }

    clearSearchResults() {
        document.querySelector('.search-input').value = '';
        this.renderSidebar();
        this.updateActiveNav(this.currentPage);
    }

    showError(message) {
        const contentContainer = document.querySelector('.docs-content');
        if (contentContainer) {
            contentContainer.classList.remove('loading');
            contentContainer.innerHTML = `
                <div class="error-message">
                    <h1>Error</h1>
                    <p>${message}</p>
                    <button onclick="location.reload()">Reload Page</button>
                </div>
            `;
        }
    }

    getErrorContent(filename) {
        const contentPath = this.config.contentPath || './content/';
        return `<div class="error-message">
            <h1>Content Not Found</h1>
            <p>Sorry, the content for "${filename}" could not be loaded.</p>
            <p>This might be because:</p>
            <ul>
                <li>The file doesn't exist yet</li>
                <li>There's a network issue</li>
                <li>The file path is incorrect</li>
            </ul>
            <p><strong>To add this content:</strong></p>
            <ol>
                <li>Create the file <code>${contentPath}${filename}</code></li>
                <li>Add your HTML content</li>
                <li>Refresh the page</li>
            </ol>
            <p><strong>Expected path:</strong> <code>${contentPath}${filename}</code></p>
        </div>`;
    }
}

// Initialize the documentation system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof docsConfig !== 'undefined') {
        console.log('Initializing DocsRenderer with config:', docsConfig);
        new DocsRenderer(docsConfig);
    } else {
        console.error('docsConfig is not defined - make sure docs-config.js is loaded');
    }
});
