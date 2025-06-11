// templates.js
// JavaScript for template loading and management with download tracking

class TemplateManager {
    constructor() {
        this.templates = [];
        this.filteredTemplates = [];
        this.currentCategory = 'all';
        this.currentSearch = '';
        this.currentPage = 1;
        this.templatesPerPage = 12;
        
        this.init();
    }
    
    async init() {
        await this.loadTemplates();
        this.setupEventListeners();
        this.renderTemplates();
        this.setupPagination();
    }
    
    async loadTemplates() {
        try {
            const response = await fetch('../../templates-api.php');
            const data = await response.json();
            
            if (data.success) {
                this.templates = data.templates;
                this.filteredTemplates = [...this.templates];
                console.log('Loaded templates:', this.templates.length);
            } else {
                console.error('Failed to load templates:', data.error);
                this.showFallbackTemplates();
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            this.showFallbackTemplates();
        }
    }
    
    showFallbackTemplates() {
        // Show a message or keep existing hardcoded templates as fallback
        console.log('Using fallback templates...');
        // Keep the existing hardcoded templates for now
    }
    
    setupEventListeners() {
        // Filter buttons
        const filterButtons = document.querySelectorAll('.filter-button');
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.setActiveFilter(button);
                this.currentCategory = button.dataset.category;
                this.filterTemplates();
                this.currentPage = 1;
                this.renderTemplates();
                this.setupPagination();
            });
        });
        
        // Search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentSearch = e.target.value;
                this.filterTemplates();
                this.currentPage = 1;
                this.renderTemplates();
                this.setupPagination();
            });
        }
    }
    
    setActiveFilter(activeButton) {
        document.querySelectorAll('.filter-button').forEach(btn => {
            btn.classList.remove('active');
        });
        activeButton.classList.add('active');
    }
    
    filterTemplates() {
        this.filteredTemplates = this.templates.filter(template => {
            const matchesCategory = this.currentCategory === 'all' || template.category === this.currentCategory;
            const matchesSearch = this.currentSearch === '' || 
                template.title.toLowerCase().includes(this.currentSearch.toLowerCase()) ||
                template.description.toLowerCase().includes(this.currentSearch.toLowerCase()) ||
                (template.tags && template.tags.some(tag => 
                    tag.toLowerCase().includes(this.currentSearch.toLowerCase())
                ));
            
            return matchesCategory && matchesSearch;
        });
    }
    
    renderTemplates() {
        const container = document.querySelector('.templates-grid');
        if (!container) return;
        
        // Calculate pagination
        const startIndex = (this.currentPage - 1) * this.templatesPerPage;
        const endIndex = startIndex + this.templatesPerPage;
        const templatesOnPage = this.filteredTemplates.slice(startIndex, endIndex);
        
        if (templatesOnPage.length === 0) {
            container.innerHTML = '<div class="no-templates"><p>No templates found matching your criteria.</p></div>';
            return;
        }
        
        container.innerHTML = templatesOnPage.map(template => this.generateTemplateCard(template)).join('');
        
        // Setup download handlers
        this.setupDownloadHandlers();
    }
    
    generateTemplateCard(template) {
        const badgeHtml = template.badge ? 
            `<div class="template-badge ${template.premium ? 'premium' : ''}">
                <span>${template.badge}</span>
            </div>` : '';
        
        const tagsHtml = template.tags && template.tags.length > 0 ?
            `<div class="template-tags">
                ${template.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
            </div>` : '';
        
        return `
            <div class="template-card" data-category="${template.category}" data-template-id="${template.id}">
                ${badgeHtml}
                <div class="template-header">
                    <div class="template-icon">
                        <i class="${template.icon}"></i>
                    </div>
                    <div class="template-meta">
                        <span class="template-category">${this.formatCategory(template.category)}</span>
                        <span class="template-downloads"><i class="fa-solid fa-download"></i> ${template.downloads.toLocaleString()}</span>
                    </div>
                </div>
                <h3>${template.title}</h3>
                <p>${template.description}</p>
                ${tagsHtml}
                <div class="template-actions">
                    <a href="#" class="template-download-btn" data-template-id="${template.id}">
                        <i class="fa-solid fa-download"></i> Download
                    </a>
                    <a href="#" class="template-preview-btn" data-template-id="${template.id}">
                        <i class="fa-solid fa-eye"></i> Preview
                    </a>
                </div>
            </div>
        `;
    }
    
    formatCategory(category) {
        return category.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
    
    setupDownloadHandlers() {
        const downloadButtons = document.querySelectorAll('.template-download-btn');
        downloadButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const templateId = parseInt(button.dataset.templateId);
                this.handleDownload(templateId);
            });
        });
        
        const previewButtons = document.querySelectorAll('.template-preview-btn');
        previewButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const templateId = parseInt(button.dataset.templateId);
                this.handlePreview(templateId);
            });
        });
    }
    
    async handleDownload(templateId) {
        const template = this.templates.find(t => t.id === templateId);
        if (!template) return;
        
        // Track the download first
        await this.trackDownload(templateId);
        
        // Generate and download the JSON file
        this.downloadTemplateJSON(template);
        
        // Show success message
        this.showSuccessMessage(`Template "${template.title}" downloaded successfully!`);
    }
    
    async trackDownload(templateId) {
        try {
            const response = await fetch('../../templates-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'track_download',
                    template_id: templateId
                })
            });
            
            const data = await response.json();
            if (data.success) {
                console.log('Download tracked successfully');
                // Update download count in UI
                this.updateDownloadCount(templateId, data.template.downloads);
            } else {
                console.error('Failed to track download:', data.error);
            }
        } catch (error) {
            console.error('Error tracking download:', error);
        }
    }
    
    updateDownloadCount(templateId, newCount) {
        const template = this.templates.find(t => t.id === templateId);
        if (template) {
            template.downloads = newCount || template.downloads + 1;
            // Update the display
            const card = document.querySelector(`[data-template-id="${templateId}"]`);
            const downloadSpan = card?.querySelector('.template-downloads');
            if (downloadSpan) {
                downloadSpan.innerHTML = `<i class="fa-solid fa-download"></i> ${template.downloads.toLocaleString()}`;
            }
        }
    }
    
    downloadTemplateJSON(template) {
        // Create template JSON structure
        const templateData = {
            metadata: {
                id: template.id,
                title: template.title,
                description: template.description,
                category: template.category,
                tags: template.tags || [],
                version: "1.0",
                created_at: template.created_at,
                created_by: "Selenix Team"
            },
            workflow: this.generateWorkflowStructure(template),
            settings: this.generateTemplateSettings(template)
        };
        
        // Create and download the file
        const jsonString = JSON.stringify(templateData, null, 2);
        const blob = new Blob([jsonString], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `${this.sanitizeFilename(template.title)}_template.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up the URL object
        URL.revokeObjectURL(url);
    }
    
    generateWorkflowStructure(template) {
        // Generate basic workflow structure based on template category
        const baseWorkflow = {
            steps: [],
            variables: {},
            conditions: []
        };
        
        switch (template.category) {
            case 'data-scraping':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{target_url}}",
                            description: "Navigate to target website"
                        },
                        {
                            id: 2,
                            type: "wait",
                            action: "wait_for_element",
                            selector: "{{main_content_selector}}",
                            timeout: 10000,
                            description: "Wait for page to load"
                        },
                        {
                            id: 3,
                            type: "extract",
                            action: "scrape_data",
                            selectors: {
                                title: "{{title_selector}}",
                                description: "{{description_selector}}",
                                price: "{{price_selector}}"
                            },
                            description: "Extract data from page"
                        },
                        {
                            id: 4,
                            type: "output",
                            action: "save_data",
                            format: "csv",
                            filename: "scraped_data.csv",
                            description: "Save extracted data"
                        }
                    ],
                    variables: {
                        target_url: "https://example.com",
                        main_content_selector: "main",
                        title_selector: "h1",
                        description_selector: ".description",
                        price_selector: ".price"
                    }
                };
                
            case 'form-filling':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{form_url}}",
                            description: "Navigate to form page"
                        },
                        {
                            id: 2,
                            type: "input",
                            action: "fill_field",
                            selector: "{{name_field_selector}}",
                            value: "{{user_name}}",
                            description: "Fill name field"
                        },
                        {
                            id: 3,
                            type: "input",
                            action: "fill_field",
                            selector: "{{email_field_selector}}",
                            value: "{{user_email}}",
                            description: "Fill email field"
                        },
                        {
                            id: 4,
                            type: "action",
                            action: "click",
                            selector: "{{submit_button_selector}}",
                            description: "Submit the form"
                        }
                    ],
                    variables: {
                        form_url: "https://example.com/form",
                        name_field_selector: "input[name='name']",
                        email_field_selector: "input[name='email']",
                        submit_button_selector: "button[type='submit']",
                        user_name: "Your Name",
                        user_email: "your.email@example.com"
                    }
                };
                
            case 'social-media':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{social_platform_url}}",
                            description: "Navigate to social media platform"
                        },
                        {
                            id: 2,
                            type: "auth",
                            action: "login",
                            username_selector: "{{username_selector}}",
                            password_selector: "{{password_selector}}",
                            login_button_selector: "{{login_button_selector}}",
                            description: "Login to platform"
                        },
                        {
                            id: 3,
                            type: "action",
                            action: "create_post",
                            content_selector: "{{post_content_selector}}",
                            content: "{{post_content}}",
                            description: "Create new post"
                        },
                        {
                            id: 4,
                            type: "action",
                            action: "click",
                            selector: "{{publish_button_selector}}",
                            description: "Publish the post"
                        }
                    ],
                    variables: {
                        social_platform_url: "https://platform.com",
                        username_selector: "input[name='username']",
                        password_selector: "input[name='password']",
                        login_button_selector: "button[type='submit']",
                        post_content_selector: "textarea[placeholder='What\\'s happening?']",
                        publish_button_selector: "button[data-testid='publish']",
                        post_content: "Your post content here"
                    }
                };
                
            case 'e-commerce':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{product_url}}",
                            description: "Navigate to product page"
                        },
                        {
                            id: 2,
                            type: "extract",
                            action: "get_price",
                            selector: "{{price_selector}}",
                            description: "Extract current price"
                        },
                        {
                            id: 3,
                            type: "condition",
                            action: "compare_price",
                            current_price: "{{extracted_price}}",
                            target_price: "{{target_price}}",
                            operator: "less_than",
                            description: "Check if price dropped"
                        },
                        {
                            id: 4,
                            type: "notification",
                            action: "send_alert",
                            message: "Price dropped to {{extracted_price}}!",
                            condition: "price_drop_detected",
                            description: "Send price alert"
                        }
                    ],
                    variables: {
                        product_url: "https://store.com/product",
                        price_selector: ".price-current",
                        target_price: "100.00"
                    }
                };
                
            case 'marketing':
                return {
                    ...baseWorkflow,
                    steps: [
                        {
                            id: 1,
                            type: "navigate",
                            action: "goto",
                            url: "{{competitor_url}}",
                            description: "Navigate to competitor website"
                        },
                        {
                            id: 2,
                            type: "extract",
                            action: "scrape_content",
                            selectors: {
                                headlines: "{{headline_selector}}",
                                pricing: "{{pricing_selector}}",
                                features: "{{features_selector}}"
                            },
                            description: "Extract competitor data"
                        },
                        {
                            id: 3,
                            type: "analysis",
                            action: "compare_data",
                            previous_data: "{{stored_data}}",
                            current_data: "{{extracted_data}}",
                            description: "Compare with previous data"
                        },
                        {
                            id: 4,
                            type: "output",
                            action: "generate_report",
                            format: "html",
                            template: "competitor_analysis_template",
                            description: "Generate analysis report"
                        }
                    ],
                    variables: {
                        competitor_url: "https://competitor.com",
                        headline_selector: "h1, h2",
                        pricing_selector: ".pricing",
                        features_selector: ".features li"
                    }
                };
                
            default:
                return baseWorkflow;
        }
    }
    
    generateTemplateSettings(template) {
        return {
            execution: {
                delay_between_steps: 1000,
                timeout_per_step: 30000,
                retry_failed_steps: true,
                max_retries: 3
            },
            browser: {
                headless: false,
                window_size: {
                    width: 1280,
                    height: 720
                },
                user_agent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            },
            output: {
                save_screenshots: true,
                log_level: "info",
                export_format: "json"
            },
            notifications: {
                on_completion: true,
                on_error: true,
                email_notifications: false
            }
        };
    }
    
    sanitizeFilename(filename) {
        return filename.replace(/[^a-z0-9]/gi, '_').toLowerCase();
    }
    
    handlePreview(templateId) {
        const template = this.templates.find(t => t.id === templateId);
        if (!template) return;
        
        if (template.preview_url) {
            window.open(template.preview_url, '_blank');
        } else {
            // Show template details modal
            this.showTemplateDetails(template);
        }
    }
    
    showTemplateDetails(template) {
        const modal = document.createElement('div');
        modal.className = 'preview-modal';
        modal.innerHTML = `
            <div class="modal-overlay" onclick="this.closest('.preview-modal').remove()">
                <div class="modal-content" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <h3>${template.title}</h3>
                        <button class="close-btn" onclick="this.closest('.preview-modal').remove()">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="template-details">
                            <div class="detail-section">
                                <h4>Description</h4>
                                <p>${template.description}</p>
                            </div>
                            <div class="detail-section">
                                <h4>Category</h4>
                                <p>${this.formatCategory(template.category)}</p>
                            </div>
                            ${template.tags && template.tags.length > 0 ? `
                                <div class="detail-section">
                                    <h4>Tags</h4>
                                    <div class="template-tags">
                                        ${template.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            <div class="detail-section">
                                <h4>Downloads</h4>
                                <p>${template.downloads.toLocaleString()} downloads</p>
                            </div>
                            <div class="detail-section">
                                <h4>What's Included</h4>
                                <ul class="included-features">
                                    <li><i class="fa-solid fa-check"></i> Pre-configured workflow steps</li>
                                    <li><i class="fa-solid fa-check"></i> Customizable variables</li>
                                    <li><i class="fa-solid fa-check"></i> Error handling logic</li>
                                    <li><i class="fa-solid fa-check"></i> Ready-to-use JSON template</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn primary-button" onclick="templateManager.handleDownload(${template.id}); this.closest('.preview-modal').remove();">
                            <i class="fa-solid fa-download"></i> Download Template
                        </button>
                        <button class="btn secondary-button" onclick="this.closest('.preview-modal').remove()">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    showSuccessMessage(message) {
        const notification = document.createElement('div');
        notification.className = 'success-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fa-solid fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 4000);
    }
    
    setupPagination() {
        const paginationContainer = document.querySelector('.templates-pagination');
        if (!paginationContainer) return;
        
        const totalPages = Math.ceil(this.filteredTemplates.length / this.templatesPerPage);
        
        if (totalPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }
        
        paginationContainer.style.display = 'flex';
        
        let paginationHTML = '';
        
        // Previous button
        if (this.currentPage > 1) {
            paginationHTML += `<button class="pagination-button prev" data-page="${this.currentPage - 1}">
                <i class="fa-solid fa-chevron-left"></i>
            </button>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `<button class="pagination-button ${i === this.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        
        // Next button
        if (this.currentPage < totalPages) {
            paginationHTML += `<button class="pagination-button next" data-page="${this.currentPage + 1}">
                <i class="fa-solid fa-chevron-right"></i>
            </button>`;
        }
        
        paginationContainer.innerHTML = paginationHTML;
        
        // Setup pagination event listeners
        paginationContainer.querySelectorAll('.pagination-button').forEach(button => {
            button.addEventListener('click', () => {
                this.currentPage = parseInt(button.dataset.page);
                this.renderTemplates();
                this.setupPagination();
                
                // Scroll to top of templates
                document.querySelector('.templates-library').scrollIntoView({ behavior: 'smooth' });
            });
        });
    }
}

// Initialize the template manager when the page loads
let templateManager;

document.addEventListener('DOMContentLoaded', function() {
    templateManager = new TemplateManager();
});

// Additional CSS for modals and notifications
const additionalStyles = `
<style>
.preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 20px;
}

.modal-body {
    padding: 30px;
    overflow-y: auto;
    max-height: 60vh;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 1px solid #eee;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
    justify-content: center;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-btn:hover {
    color: #333;
    background: #e9ecef;
}

.detail-section {
    margin-bottom: 25px;
}

.detail-section h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.detail-section p {
    margin: 0;
    line-height: 1.6;
    color: #666;
}

.included-features {
    margin: 0;
    padding: 0;
    list-style: none;
}

.included-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: #666;
}

.included-features i {
    color: #28a745;
    font-size: 12px;
}

.success-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1001;
    animation: slideIn 0.3s ease-out;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.no-templates {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-templates p {
    font-size: 18px;
    margin: 0;
}

.templates-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 40px;
}

.pagination-button {
    background: white;
    border: 1px solid #ddd;
    color: #333;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
}

.pagination-button:hover {
    background: #f8f9fa;
    border-color: #667eea;
}

.pagination-button.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn.primary-button {
    background: #667eea;
    color: white;
}

.btn.primary-button:hover {
    background: #5a6fd8;
}

.btn.secondary-button {
    background: #6c757d;
    color: white;
}

.btn.secondary-button:hover {
    background: #5a6268;
}
</style>
`;

// Inject the additional styles
document.head.insertAdjacentHTML('beforeend', additionalStyles);