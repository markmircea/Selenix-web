// JavaScript for Templates page
document.addEventListener('DOMContentLoaded', function() {
    // Template filtering functionality
    const filterButtons = document.querySelectorAll('.filter-button');
    const templateCards = document.querySelectorAll('.template-card');
    
    // Initialize filter functionality
    if (filterButtons.length > 0 && templateCards.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.dataset.category;
                
                // Filter templates
                templateCards.forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput && templateCards.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            templateCards.forEach(card => {
                const templateTitle = card.querySelector('h3').textContent.toLowerCase();
                const templateDescription = card.querySelector('p').textContent.toLowerCase();
                const templateCategory = card.dataset.category.toLowerCase();
                const tags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());
                
                // Check if the search term is in the title, description, category, or tags
                const matchesSearch = 
                    templateTitle.includes(searchTerm) || 
                    templateDescription.includes(searchTerm) || 
                    templateCategory.includes(searchTerm) ||
                    tags.some(tag => tag.includes(searchTerm));
                
                if (matchesSearch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Reset category filter if searching
            if (searchTerm) {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                document.querySelector('[data-category="all"]').classList.add('active');
            }
        });
    }
    
    // Pagination functionality (simplified for demo)
    const paginationButtons = document.querySelectorAll('.pagination-button');
    
    if (paginationButtons.length > 0) {
        paginationButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (this.classList.contains('next')) {
                    // Logic to go to next page would go here
                    console.log('Navigate to next page');
                } else {
                    paginationButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the appropriate page
                    console.log('Navigate to page ' + this.textContent);
                }
            });
        });
    }
    
    // Template preview functionality
    const previewButtons = document.querySelectorAll('.template-preview-btn');
    
    if (previewButtons.length > 0) {
        previewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const templateCard = this.closest('.template-card');
                const templateTitle = templateCard.querySelector('h3').textContent;
                
                // In a real implementation, this would open a modal or navigate to a preview page
                console.log('Preview template: ' + templateTitle);
                
                // Example alert for demo purposes
                alert('Preview for template: ' + templateTitle + '\n\nIn the actual implementation, this would show a detailed preview of the template.');
            });
        });
    }
    
    // Template download functionality
    const downloadButtons = document.querySelectorAll('.template-download-btn');
    
    if (downloadButtons.length > 0) {
        downloadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const templateCard = this.closest('.template-card');
                const templateTitle = templateCard.querySelector('h3').textContent;
                
                // In a real implementation, this would trigger a download
                console.log('Download template: ' + templateTitle);
                
                // Simulate download with a success message
                const downloadMessage = document.createElement('div');
                downloadMessage.className = 'download-message';
                downloadMessage.innerHTML = `
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Template "${templateTitle}" is downloading...</span>
                `;
                
                document.body.appendChild(downloadMessage);
                
                // Remove the message after a few seconds
                setTimeout(() => {
                    downloadMessage.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(downloadMessage);
                    }, 300);
                }, 3000);
            });
        });
    }
    
    // Add CSS for download message
    const style = document.createElement('style');
    style.textContent = `
        .download-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #10B981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: opacity 0.3s ease;
        }
        
        .download-message i {
            font-size: 1.5rem;
        }
    `;
    document.head.appendChild(style);
});
