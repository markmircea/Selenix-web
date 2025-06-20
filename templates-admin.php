<?php
// templates-admin.php
// Admin panel for managing templates

// Basic password protection (change this!)
$ADMIN_PASSWORD = 'selenix2024';

session_start();

// Check authentication
if (!isset($_SESSION['templates_admin_logged_in'])) {
    if (($_POST['password'] ?? '') === $ADMIN_PASSWORD) {
        $_SESSION['templates_admin_logged_in'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Logout
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: templates-admin.php');
    exit;
}

// Database connection
$host = 'localhost';
$username = 'aibrainl_selenix';
$password = 'She-wolf11';
$database = 'aibrainl_selenix';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_template':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $long_description = trim($_POST['long_description'] ?? '');
                $category = $_POST['category'];
                $icon = trim($_POST['icon']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $premium = isset($_POST['premium']) ? 1 : 0;
                $badge = trim($_POST['badge']) ?: null;
                $tags = $_POST['tags'] ? json_encode(array_map('trim', explode(',', $_POST['tags']))) : null;
                $preview_url = trim($_POST['preview_url'] ?? '') ?: null;
                $preview_image = trim($_POST['preview_image'] ?? '') ?: null;
                $image_alt = trim($_POST['image_alt'] ?? '') ?: null;
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO templates (title, description, long_description, category, icon, featured, premium, badge, tags, preview_url, preview_image, image_alt, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$title, $description, $long_description, $category, $icon, $featured, $premium, $badge, $tags, $preview_url, $preview_image, $image_alt, $status])) {
                    $message = 'Template added successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error adding template.';
                    $messageType = 'error';
                }
                break;
                
            case 'edit_template':
                $id = (int)$_POST['template_id'];
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $long_description = trim($_POST['long_description'] ?? '');
                $category = $_POST['category'];
                $icon = trim($_POST['icon']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $premium = isset($_POST['premium']) ? 1 : 0;
                $badge = trim($_POST['badge']) ?: null;
                $tags = $_POST['tags'] ? json_encode(array_map('trim', explode(',', $_POST['tags']))) : null;
                $preview_url = trim($_POST['preview_url'] ?? '') ?: null;
                $preview_image = trim($_POST['preview_image'] ?? '') ?: null;
                $image_alt = trim($_POST['image_alt'] ?? '') ?: null;
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("
                    UPDATE templates 
                    SET title=?, description=?, long_description=?, category=?, icon=?, featured=?, premium=?, badge=?, tags=?, preview_url=?, preview_image=?, image_alt=?, status=?, updated_at=NOW()
                    WHERE id=?
                ");
                
                if ($stmt->execute([$title, $description, $long_description, $category, $icon, $featured, $premium, $badge, $tags, $preview_url, $preview_image, $image_alt, $status, $id])) {
                    $message = 'Template updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating template.';
                    $messageType = 'error';
                }
                break;
                
            case 'delete_template':
                $id = (int)$_POST['template_id'];
                $stmt = $pdo->prepare("DELETE FROM templates WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'Template deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting template.';
                    $messageType = 'error';
                }
                break;
                
            case 'increment_downloads':
                $id = (int)$_POST['template_id'];
                $stmt = $pdo->prepare("UPDATE templates SET downloads = downloads + 1 WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'Download count incremented!';
                    $messageType = 'success';
                } else {
                    $message = 'Error incrementing downloads.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Helper function to generate template filename from title
function generateTemplateFilename($title) {
    // Remove special characters and convert to lowercase
    $filename = preg_replace('/[^a-zA-Z0-9\s]/', '', $title);
    $filename = preg_replace('/\s+/', '_', trim($filename));
    $filename = strtolower($filename);
    return $filename . '.json';
}

// Handle file uploads
if (isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/templates/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type (JSON only)
    $fileInfo = pathinfo($_FILES['template_file']['name']);
    if (strtolower($fileInfo['extension']) !== 'json') {
        $message = 'Only JSON files are allowed for templates.';
        $messageType = 'error';
    } else {
        // Generate filename based on template title
        $templateTitle = isset($_POST['title']) ? trim($_POST['title']) : '';
        if (empty($templateTitle)) {
            $message = 'Template title is required for file upload.';
            $messageType = 'error';
        } else {
            $fileName = generateTemplateFilename($templateTitle);
            $uploadPath = $uploadDir . $fileName;
            
            // Check if file already exists and handle conflicts
            $counter = 1;
            $originalFileName = $fileName;
            while (file_exists($uploadPath)) {
                $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);
                $fileName = $fileNameWithoutExt . '_' . $counter . '.json';
                $uploadPath = $uploadDir . $fileName;
                $counter++;
            }
            
            if (move_uploaded_file($_FILES['template_file']['tmp_name'], $uploadPath)) {
                // If this is part of a template form submission, use the uploaded file path
                if (isset($_POST['action']) && ($_POST['action'] === 'add_template' || $_POST['action'] === 'edit_template')) {
                    $_POST['file_path'] = $uploadPath; // Set the uploaded file path
                }
                $message = 'Template file uploaded successfully: ' . $fileName;
                $messageType = 'success';
            } else {
                $message = 'Error uploading template file.';
                $messageType = 'error';
            }
        }
    }
}

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Templates Admin Login - Selenix</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            input[type="password"] { width: 200px; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        </style>
    </head>
    <body>
        <form method="POST" class="login-form">
            <h2>Selenix Templates Admin</h2>
            <div>
                <input type="password" name="password" placeholder="Admin Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    <?php
}

// Get all templates
$templates = $pdo->query("SELECT * FROM templates ORDER BY created_at DESC")->fetchAll();

// Get statistics
$stats = $pdo->query("SELECT 
    COUNT(*) as total_templates,
    SUM(downloads) as total_downloads,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_templates,
    COUNT(CASE WHEN featured = 1 THEN 1 END) as featured_templates,
    COUNT(CASE WHEN premium = 1 THEN 1 END) as premium_templates
    FROM templates")->fetch();

// Get categories
$categories = $pdo->query("SELECT category, COUNT(*) as count FROM templates GROUP BY category ORDER BY count DESC")->fetchAll();

// Get template to edit if requested
$editTemplate = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editTemplate = $pdo->query("SELECT * FROM templates WHERE id = $editId")->fetch();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Selenix Templates Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="templates-admin.css">
    <!-- Include Quill.js for rich text editing (free alternative to TinyMCE) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>
<body>
    <div class="header">
        <h1><i class="fa-solid fa-cogs"></i> Selenix Templates Admin Panel</h1>
        <a href="?action=logout" class="logout">Logout</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_templates']; ?></div>
                <div class="stat-label">Total Templates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_templates']; ?></div>
                <div class="stat-label">Active Templates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_downloads']; ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['featured_templates']; ?></div>
                <div class="stat-label">Featured Templates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['premium_templates']; ?></div>
                <div class="stat-label">Premium Templates</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <button class="btn btn-primary" onclick="openModal('addModal')">
                <i class="fa-solid fa-plus"></i> Add New Template
            </button>
            <button class="btn btn-secondary" onclick="openModal('uploadModal')">
                <i class="fa-solid fa-upload"></i> Upload File
            </button>
            <a href="product/templates/index.html" class="btn btn-success" target="_blank">
                <i class="fa-solid fa-eye"></i> View Templates Page
            </a>
        </div>
        
        <div class="section">
            <div class="section-header">
                <span>Template Analytics</span>
                <span>Quick overview</span>
            </div>
            <div class="section-content">
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <h4>Most Downloaded Templates</h4>
                        <div class="top-templates">
                            <?php 
                            $topTemplates = $pdo->query("SELECT title, downloads FROM templates ORDER BY downloads DESC LIMIT 5")->fetchAll();
                            foreach ($topTemplates as $template): 
                            ?>
                                <div class="top-template-item">
                                    <span class="template-name"><?php echo htmlspecialchars($template['title']); ?></span>
                                    <span class="download-count"><?php echo number_format($template['downloads']); ?> downloads</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h4>Downloads by Category</h4>
                        <div class="category-stats">
                            <?php 
                            $categoryStats = $pdo->query("SELECT category, SUM(downloads) as total_downloads FROM templates GROUP BY category ORDER BY total_downloads DESC")->fetchAll();
                            foreach ($categoryStats as $stat): 
                            ?>
                                <div class="category-stat-item">
                                    <span class="category-name"><?php echo ucfirst(str_replace('-', ' ', $stat['category'])); ?></span>
                                    <span class="category-downloads"><?php echo number_format($stat['total_downloads']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="analytics-card">
                        <h4>Quick Actions</h4>
                        <div class="quick-actions-list">
                            <a href="admin.php" class="action-link" target="_blank">
                                <i class="fa-solid fa-chart-line"></i> View Detailed Analytics
                            </a>
                            <a href="product/templates/index.html" class="action-link" target="_blank">
                                <i class="fa-solid fa-eye"></i> View Templates Page
                            </a>
                            <button class="action-link" onclick="openModal('uploadModal')">
                                <i class="fa-solid fa-upload"></i> Upload New Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <span>All Templates</span>
                <span><?php echo count($templates); ?> templates</span>
            </div>
            <div class="section-content">
                <div class="templates-grid">
                    <?php foreach ($templates as $template): ?>
                        <div class="template-card">
                            <div class="template-header">
                                <div class="template-meta">
                                    <i class="<?php echo htmlspecialchars($template['icon']); ?>"></i>
                                    <span><?php echo ucfirst(str_replace('-', ' ', $template['category'])); ?></span>
                                    <span class="status-<?php echo $template['status']; ?>">
                                        <?php echo ucfirst($template['status']); ?>
                                    </span>
                                </div>
                                <?php if ($template['badge']): ?>
                                    <span class="badge badge-<?php echo strtolower($template['badge']); ?>">
                                        <?php echo htmlspecialchars($template['badge']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($template['preview_image']): ?>
                                <div class="template-image">
                                    <img src="<?php echo htmlspecialchars($template['preview_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($template['image_alt'] ?: $template['title']); ?>" 
                                         style="width: 100%; height: 120px; object-fit: cover; object-position: center; border-radius: 4px; background: #f8f9fa;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="template-title">
                                <?php echo htmlspecialchars($template['title']); ?>
                            </div>
                            
                            <div class="template-description">
                                <?php 
                                echo htmlspecialchars(substr($template['description'], 0, 100)); 
                                echo strlen($template['description']) > 100 ? '...' : ''; 
                                ?>
                            </div>
                            
                            <?php if ($template['long_description'] && strlen($template['long_description']) > strlen($template['description'])): ?>
                                <div class="has-long-description">
                                    <i class="fa-solid fa-file-text"></i> Has detailed description
                                </div>
                            <?php endif; ?>
                            
                            <div class="template-meta">
                                <span><i class="fa-solid fa-download"></i> <?php echo number_format($template['downloads']); ?></span>
                                <span><i class="fa-solid fa-calendar"></i> <?php echo date('M j, Y', strtotime($template['created_at'])); ?></span>
                                <?php 
                                $expectedFilename = generateTemplateFilename($template['title']);
                                $expectedFilePath = 'uploads/templates/' . $expectedFilename;
                                if (file_exists($expectedFilePath)): 
                                ?>
                                    <span style="color: #28a745;"><i class="fa-solid fa-file-check"></i> File available</span>
                                <?php else: ?>
                                    <span style="color: #dc3545;"><i class="fa-solid fa-file-excel"></i> No file</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($template['tags']): ?>
                                <div class="template-tags">
                                    <?php foreach (json_decode($template['tags']) as $tag): ?>
                                        <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="template-actions">
                                <a href="?edit=<?php echo $template['id']; ?>" class="btn btn-warning">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="increment_downloads">
                                    <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-plus"></i> +1 Download
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                    <input type="hidden" name="action" value="delete_template">
                                    <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Template Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content modal-wide" onclick="event.stopPropagation();">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editTemplate ? 'Edit Template' : 'Add New Template'; ?></h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editTemplate ? 'edit_template' : 'add_template'; ?>">
                <?php if ($editTemplate): ?>
                    <input type="hidden" name="template_id" value="<?php echo $editTemplate['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Template Title</label>
                        <input type="text" name="title" required value="<?php echo $editTemplate ? htmlspecialchars($editTemplate['title']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="data-scraping" <?php echo ($editTemplate && $editTemplate['category'] == 'data-scraping') ? 'selected' : ''; ?>>Data Scraping</option>
                            <option value="form-filling" <?php echo ($editTemplate && $editTemplate['category'] == 'form-filling') ? 'selected' : ''; ?>>Form Filling</option>
                            <option value="social-media" <?php echo ($editTemplate && $editTemplate['category'] == 'social-media') ? 'selected' : ''; ?>>Social Media</option>
                            <option value="e-commerce" <?php echo ($editTemplate && $editTemplate['category'] == 'e-commerce') ? 'selected' : ''; ?>>E-Commerce</option>
                            <option value="marketing" <?php echo ($editTemplate && $editTemplate['category'] == 'marketing') ? 'selected' : ''; ?>>Marketing</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Icon (Font Awesome class)</label>
                        <input type="text" name="icon" placeholder="fa-solid fa-cog" value="<?php echo $editTemplate ? htmlspecialchars($editTemplate['icon']) : 'fa-solid fa-cog'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Badge (optional)</label>
                        <input type="text" name="badge" placeholder="Featured, New, Premium" value="<?php echo $editTemplate ? htmlspecialchars($editTemplate['badge']) : ''; ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Description (shows on index page)</label>
                        <div id="description-editor" style="height: 200px;"></div>
                        <textarea id="description" name="description" style="display: none;" required><?php echo $editTemplate ? htmlspecialchars($editTemplate['description']) : ''; ?></textarea>
                        <small>This appears on the main templates page. Keep it concise and engaging (recommended: under 150 characters).</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Long Description (shows in preview with formatting)</label>
                        <div id="long-description-editor" style="height: 300px;"></div>
                        <textarea id="long-description" name="long_description" style="display: none;"><?php echo $editTemplate ? htmlspecialchars($editTemplate['long_description']) : ''; ?></textarea>
                        <small>This appears in the preview modal and supports rich text formatting.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Preview Image URL</label>
                        <input type="url" name="preview_image" placeholder="https://example.com/image.jpg" value="<?php echo $editTemplate ? htmlspecialchars($editTemplate['preview_image'] ?? '') : ''; ?>">
                        <small>Image that shows in the preview modal (recommended: 600x400px)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Image Alt Text</label>
                        <input type="text" name="image_alt" placeholder="Descriptive text for the image" value="<?php echo $editTemplate ? htmlspecialchars($editTemplate['image_alt'] ?? '') : ''; ?>">
                        <small>Accessibility description for the preview image</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Upload Template File (JSON)</label>
                        <div class="upload-area">
                            <i class="fa-solid fa-cloud-upload" style="font-size: 2em; color: #ddd; margin-bottom: 10px;"></i>
                            <p>Choose a JSON file to upload (optional)</p>
                            <input type="file" name="template_file" accept=".json">
                            <?php if ($editTemplate && $editTemplate['file_path']): ?>
                                <p style="margin-top: 10px; color: #28a745;">
                                    <i class="fa-solid fa-check"></i> Current file: <?php echo basename($editTemplate['file_path']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    
                    <div class="form-group">
                        <label>Preview URL (optional)</label>
                        <input type="text" name="preview_url" placeholder="https://example.com/preview" value="<?php echo $editTemplate ? htmlspecialchars($editTemplate['preview_url'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" required><?php echo $editTemplate ? htmlspecialchars($editTemplate['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Tags (comma-separated)</label>
                        <input type="text" name="tags" placeholder="tag1, tag2, tag3" value="<?php echo $editTemplate ? implode(', ', json_decode($editTemplate['tags'] ?: '[]')) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($editTemplate && $editTemplate['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editTemplate && $editTemplate['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="draft" <?php echo ($editTemplate && $editTemplate['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="featured" <?php echo ($editTemplate && $editTemplate['featured']) ? 'checked' : ''; ?>>
                                Featured Template
                            </label>
                            <label>
                                <input type="checkbox" name="premium" <?php echo ($editTemplate && $editTemplate['premium']) ? 'checked' : ''; ?>>
                                Premium Template
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $editTemplate ? 'Update Template' : 'Add Template'; ?>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
            </form>
        </div>
    </div>
    
    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content" onclick="event.stopPropagation();">
            <span class="close" onclick="closeModal('uploadModal')">&times;</span>
            <h2>Upload Template File</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Template Title (for filename)</label>
                    <input type="text" name="title" placeholder="Enter template title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #666;">This will be used to generate the filename: template_title.json</small>
                </div>
                
                <div class="upload-area">
                    <i class="fa-solid fa-cloud-upload" style="font-size: 3em; color: #ddd; margin-bottom: 20px;"></i>
                    <p>Choose a JSON template file to upload</p>
                    <input type="file" name="template_file" accept=".json" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Upload File</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('uploadModal')">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Auto-open edit modal if editing
        <?php if ($editTemplate): ?>
            openModal('addModal');
        <?php endif; ?>
        
        // Initialize Quill.js editors for rich text editing
        
        // Description editor (shorter toolbar for brief descriptions)
        var descriptionQuill = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Enter brief description for template cards...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'color': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });
        
        // Long description editor (full toolbar)
        var longDescriptionQuill = new Quill('#long-description-editor', {
            theme: 'snow',
            placeholder: 'Enter detailed description with formatting...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });
        
        // Load existing content if editing
        var existingDescription = document.getElementById('description').value;
        if (existingDescription) {
            descriptionQuill.root.innerHTML = existingDescription;
        }
        
        var existingLongDescription = document.getElementById('long-description').value;
        if (existingLongDescription) {
            longDescriptionQuill.root.innerHTML = existingLongDescription;
        }
        
        // Update hidden textareas when content changes
        descriptionQuill.on('text-change', function() {
            document.getElementById('description').value = descriptionQuill.root.innerHTML;
        });
        
        longDescriptionQuill.on('text-change', function() {
            document.getElementById('long-description').value = longDescriptionQuill.root.innerHTML;
        });
        
        // Update hidden textareas before form submission
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('description').value = descriptionQuill.root.innerHTML;
            document.getElementById('long-description').value = longDescriptionQuill.root.innerHTML;
        });
    </script>
</body>
</html>
