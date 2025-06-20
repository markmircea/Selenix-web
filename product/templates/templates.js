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
        this.apiBaseUrl = '/templates-api.php';
        
        this.init();
    }
    
    async init() {
        console.log('Initializing TemplateManager...');
        await this.loadTemplates();
        this.setupEventListeners();
        this.renderTemplates();
        this.setupPagination();
    }
    
    async loadTemplates() {
        try {
            console.log('Attempting to fetch templates from:', this.apiBaseUrl);
            
            const response = await fetch(this.apiBaseUrl);
            console.log('Templates API response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data);
            
            if (data.success && data.templates) {
                this.templates = data.templates;
                this.filteredTemplates = [...this.templates];
                console.log('Loaded templates:', this.templates.length);
                
                // Log file paths for debugging
                this.templates.forEach(template => {
                    console.log(`Template "${template.title}" file_path:`, template.file_path);
                });
                
                return true;
            } else {
                console.error('API returned error:', data.error || 'Unknown error');
                throw new Error(data.error || 'Failed to load templates');
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            this.showFallbackTemplates();
            return false;
        }
    }
    
    showFallbackTemplates() {
        const container = document.querySelector('.templates-grid');
        if (container) {
            container.innerHTML = `
                <div class="no-templates">
                    <div class="error-content">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <h3>Unable to Load Templates</h3>
                        <p>We're having trouble connecting to the template database.</p>
                        <div class="error-details">
                            <p><strong>For developers:</strong> Check the browser console for details.</p>
                            <p>API URL: <code>${this.apiBaseUrl}</code></p>
                        </div>
                        <button onclick="window.templateManager.loadTemplates().then(() => window.templateManager.renderTemplates())" class="retry-btn">
                            <i class="fa-solid fa-refresh"></i> Try Again
                        </button>
                    </div>
                </div>
            `;
        }
        
        const pagination = document.querySelector('.templates-pagination');
        if (pagination) pagination.style.display = 'none';
    }
    
    setupEventListeners() {
        // Filter buttons
        document.querySelectorAll('.filter-button').forEach(button => {
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
        document.querySelectorAll('.filter-button').forEach(btn => btn.classList.remove('active'));
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
        
        const startIndex = (this.currentPage - 1) * this.templatesPerPage;
        const endIndex = startIndex + this.templatesPerPage;
        const templatesOnPage = this.filteredTemplates.slice(startIndex, endIndex);
        
        if (templatesOnPage.length === 0) {
            container.innerHTML = `
                <div class="no-templates">
                    <i class="fa-solid fa-search"></i>
                    <h3>No templates found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            `;
            return;
        }
        
        // Clear existing content and replace with dynamic content
        container.innerHTML = templatesOnPage.map(template => this.generateTemplateCard(template)).join('');
        this.setupDownloadHandlers();
    }
    
    generateTemplateCard(template) {
        const badgeHtml = template.badge ? 
            `<div class="template-badge ${template.premium ? 'premium' : ''}">
                <span>${template.badge}</span>
            </div>` : '';
        
        const tagsHtml = template.tags && template.tags.length > 0 ?
            `<div class="template-tags">
                ${template.tags.map(tag => `<span class="tag">${this.escapeHtml(tag)}</span>`).join('')}
            </div>` : '';
        
        const downloadButtonHtml = `<a href="#" class="template-download-btn" data-template-id="${template.id}">
            <i class="fa-solid fa-download"></i> Download
        </a>`;
        
        return `
            <div class="template-card" data-category="${template.category}" data-template-id="${template.id}">
                ${badgeHtml}
                <div class="template-header">
                    <div class="template-icon">
                        <i class="${template.icon || 'fa-solid fa-cog'}"></i>
                    </div>
                    <div class="template-meta">
                        <span class="template-category">${this.formatCategory(template.category)}</span>
                        <span class="template-downloads">
                            <i class="fa-solid fa-download"></i> ${parseInt(template.downloads || 0).toLocaleString()}
                        </span>
                    </div>
                </div>
                <h3>${this.escapeHtml(template.title)}</h3>
                <p>${this.escapeHtml(template.description)}</p>
                ${tagsHtml}
                <div class="template-actions">
                    ${downloadButtonHtml}
                    <a href="#" class="template-preview-btn" data-template-id="${template.id}">
                        <i class="fa-solid fa-eye"></i> Preview
                    </a>
                </div>
            </div>
        `;
    }
    
    setupDownloadHandlers() {
        document.querySelectorAll('.template-download-btn:not(.disabled)').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleDownload(parseInt(button.dataset.templateId));
            });
        });
        
        document.querySelectorAll('.template-preview-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handlePreview(parseInt(button.dataset.templateId));
            });
        });
    }
    
    async handleDownload(templateId) {
        const template = this.templates.find(t => t.id === templateId);
        if (!template) return;
        
        // Get the file path (with fallback logic)
        const filePath = this.getTemplateFilePath(template);
        
        // Check if we have a valid file path
        if (!filePath || filePath.trim() === '') {
            this.showNotification('No template file available for download. Please contact support.', 'error');
            return;
        }
        
        const downloadBtn = document.querySelector(`[data-template-id="${templateId}"] .template-download-btn`);
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Downloading...';
        downloadBtn.style.pointerEvents = 'none';
        
        try {
            await this.trackDownload(templateId);
            
            // Create download URL - handle both relative and absolute paths
            let downloadUrl = filePath;
            if (!downloadUrl.startsWith('http')) {
                // If it's a relative path, make it absolute
                downloadUrl = window.location.origin + '/' + downloadUrl.replace(/^\//, '');
            }
            

            // Force download instead of opening in browser
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = this.generateTemplateFilename(template.title);
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.showNotification(`Template "${template.title}" downloaded successfully!`, 'success');
            
        } catch (error) {
            console.error('Download error:', error);
            this.showNotification('Failed to download template. Please try again.', 'error');
        } finally {
            setTimeout(() => {
                downloadBtn.innerHTML = originalText;
                downloadBtn.style.pointerEvents = 'auto';
            }, 1000);
        }
    }
    
    async trackDownload(templateId) {
        try {
            const response = await fetch(this.apiBaseUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'track_download', template_id: templateId })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.updateDownloadCount(templateId, data.template?.downloads);
                }
            }
        } catch (error) {
            console.error('Error tracking download:', error);
        }
    }
    
    updateDownloadCount(templateId, newCount) {
        const template = this.templates.find(t => t.id === templateId);
        if (template && newCount !== undefined) {
            template.downloads = newCount;
            const card = document.querySelector(`[data-template-id="${templateId}"]`);
            const downloadSpan = card?.querySelector('.template-downloads');
            if (downloadSpan) {
                downloadSpan.innerHTML = `<i class="fa-solid fa-download"></i> ${parseInt(newCount).toLocaleString()}`;
            }
        }
    }
    
    handlePreview(templateId) {
        const template = this.templates.find(t => t.id === templateId);
        if (!template) return;
        
        if (template.preview_url && template.preview_url.trim() !== '') {
            window.open(template.preview_url, '_blank');
        } else {
            this.showTemplateDetails(template);
        }
    }
    
    showTemplateDetails(template) {
        const modal = document.createElement('div');
        modal.className = 'preview-modal';
        
        // Use long description if available, otherwise fall back to regular description
        const detailedDescription = template.long_description || template.description;
        
        // Create preview image HTML if available
        const previewImageHtml = template.preview_image ? 
            `<div class="detail-section">
                <h4>Preview</h4>
                <div class="preview-image-container" style="text-align: center; margin: 20px 0;">
                    <img src="${this.escapeHtml(template.preview_image)}" 
                         alt="${this.escapeHtml(template.image_alt || template.title)}" 
                         style="max-width: 100%; max-height: 400px; width: auto; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); object-fit: contain; background: #f8f9fa;">
                </div>
            </div>` : '';
        
        modal.innerHTML = `
            <div class="modal-overlay" onclick="this.closest('.preview-modal').remove()">
                <div class="modal-content modal-large" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <h3>${this.escapeHtml(template.title)}</h3>
                        <button class="close-btn" onclick="this.closest('.preview-modal').remove()">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="template-details">
                            ${previewImageHtml}
                            <div class="detail-section">
                                <h4>Description</h4>
                                <div class="formatted-description">${detailedDescription}</div>
                            </div>
                            <div class="detail-section">
                                <h4>Category</h4>
                                <p>${this.formatCategory(template.category)}</p>
                            </div>
                            ${template.tags && template.tags.length > 0 ? `
                                <div class="detail-section">
                                    <h4>Tags</h4>
                                    <div class="template-tags">
                                        ${template.tags.map(tag => `<span class="tag">${this.escapeHtml(tag)}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            <div class="detail-section">
                                <h4>Downloads</h4>
                                <p>${parseInt(template.downloads || 0).toLocaleString()} downloads</p>
                            </div>
                            <div class="detail-section">
                                <h4>Filename</h4>
                                <p><code>${this.generateTemplateFilename(template.title)}</code></p>
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
    
    showNotification(message, type = 'success') {
        document.querySelectorAll('.notification').forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fa-solid ${icon}"></i>
                <span>${this.escapeHtml(message)}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('slide-out');
                setTimeout(() => notification.remove(), 300);
            }
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
        
        if (this.currentPage > 1) {
            paginationHTML += `<button class="pagination-button prev" data-page="${this.currentPage - 1}">
                <i class="fa-solid fa-chevron-left"></i>
            </button>`;
        }
        
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `<button class="pagination-button ${i === this.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        
        if (this.currentPage < totalPages) {
            paginationHTML += `<button class="pagination-button next" data-page="${this.currentPage + 1}">
                <i class="fa-solid fa-chevron-right"></i>
            </button>`;
        }
        
        paginationContainer.innerHTML = paginationHTML;
        
        paginationContainer.querySelectorAll('.pagination-button').forEach(button => {
            button.addEventListener('click', () => {
                this.currentPage = parseInt(button.dataset.page);
                this.renderTemplates();
                this.setupPagination();
                document.querySelector('.templates-library').scrollIntoView({ behavior: 'smooth' });
            });
        });
    }
    
    // Utility methods
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    formatCategory(category) {
        return category.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
    
    sanitizeFilename(filename) {
        return filename.replace(/[^a-z0-9]/gi, '_').toLowerCase();
    }
    
    // Generate template filename from title (matches PHP backend logic)
    generateTemplateFilename(title) {
        // Remove special characters and convert to lowercase
        let filename = title.replace(/[^a-zA-Z0-9\s]/g, '');
        filename = filename.replace(/\s+/g, '_').trim();
        filename = filename.toLowerCase();
        return filename + '.json';
    }
    
    // Get the expected file path for a template
    getTemplateFilePath(template) {
        // Always generate path from template title (no database file_path dependency)
        const expectedFilename = this.generateTemplateFilename(template.title);
        return `uploads/templates/${expectedFilename}`;
    }
}

// Initialize when DOM is ready
let templateManager;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing Template Manager...');
    templateManager = new TemplateManager();
    window.templateManager = templateManager; // For debugging
    console.log('Template Manager initialized');
});
