<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';

requireAdmin();

$blogModel = new BlogModel();
$isEdit = isset($_GET['id']);
$postId = $isEdit ? intval($_GET['id']) : null;
$post = null;

if ($isEdit) {
    $post = $blogModel->getPostForEdit($postId);
    if (!$post) {
        header('Location: admin-posts.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = $_POST['content']; // Don't sanitize content as it may contain HTML
    $excerpt = sanitizeInput($_POST['excerpt']);
    $category = sanitizeInput($_POST['category']);
    $authorName = sanitizeInput($_POST['author_name']);
    $authorTitle = sanitizeInput($_POST['author_title']);
    $readTime = intval($_POST['read_time']);
    $metaTitle = sanitizeInput($_POST['meta_title']);
    $metaDescription = sanitizeInput($_POST['meta_description']);
    $isFeatured = isset($_POST['is_featured']) ? 't' : 'f';
    $isPublished = isset($_POST['is_published']) ? 't' : 'f';
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    if (empty($authorName)) {
        $errors[] = 'Author name is required';
    }
    
    if ($readTime < 1) {
        $readTime = estimateReadingTime($content);
    }
    
    // Handle featured image upload
    $featuredImage = $isEdit ? $post['featured_image'] : '';
    
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload($_FILES['featured_image']);
        
        if ($uploadResult['success']) {
            // Delete old image if editing
            if ($isEdit && !empty($post['featured_image'])) {
                deleteUploadedFile($post['featured_image']);
            }
            $featuredImage = $uploadResult['filename'];
        } else {
            $errors[] = 'Error uploading image: ' . $uploadResult['error'];
        }
    }
    
    // Handle author avatar upload
    $authorAvatar = $isEdit ? $post['author_avatar'] : '';
    
    if (isset($_FILES['author_avatar']) && $_FILES['author_avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload($_FILES['author_avatar']);
        
        if ($uploadResult['success']) {
            // Delete old avatar if editing
            if ($isEdit && !empty($post['author_avatar'])) {
                deleteUploadedFile($post['author_avatar']);
            }
            $authorAvatar = $uploadResult['filename'];
        } else {
            $errors[] = 'Error uploading author avatar: ' . $uploadResult['error'];
        }
    }
    
    if (empty($errors)) {
        // Generate slug
        if ($isEdit) {
            $slug = $blogModel->generateUniqueSlug($title, $postId);
        } else {
            $slug = $blogModel->generateUniqueSlug($title);
        }
        
        // Auto-generate excerpt if empty
        if (empty($excerpt)) {
            $excerpt = generateExcerpt($content);
        }
        
        // Auto-generate meta title if empty
        if (empty($metaTitle)) {
            $metaTitle = $title . ' - ' . BLOG_TITLE;
        }
        
        // Auto-generate meta description if empty
        if (empty($metaDescription)) {
            $metaDescription = $excerpt;
        }
        
        $publishedAt = null;
        if ($isPublished === 't') {
            if ($isEdit && $post['is_published'] === 'f') {
                // Publishing for the first time
                $publishedAt = date('Y-m-d H:i:s');
            } elseif ($isEdit) {
                // Keep existing published date
                $publishedAt = $post['published_at'];
            } else {
                // New published post
                $publishedAt = date('Y-m-d H:i:s');
            }
        }
        
        $postData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'category' => $category,
            'featured_image' => $featuredImage,
            'is_featured' => $isFeatured,
            'is_published' => $isPublished,
            'author_name' => $authorName,
            'author_title' => $authorTitle,
            'author_avatar' => $authorAvatar,
            'read_time' => $readTime,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'published_at' => $publishedAt
        ];
        
        if ($isEdit) {
            if ($blogModel->updatePost($postId, $postData)) {
                header('Location: admin-posts.php?message=updated');
                exit;
            } else {
                $errors[] = 'Error updating post';
            }
        } else {
            $newPostId = $blogModel->createPost($postData);
            if ($newPostId) {
                header('Location: admin-posts.php?message=created');
                exit;
            } else {
                $errors[] = 'Error creating post';
            }
        }
    }
}

global $BLOG_CATEGORIES;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit Post' : 'Add New Post'; ?> - Selenix Blog Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="blog-styles.css">
    <link rel="stylesheet" href="admin-styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>
                    <span class="logo-text">selenix<span class="logo-dot">.</span>io</span>
                    <span class="admin-label">Admin</span>
                </h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin-dashboard.php"><i class="fa-solid fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="admin-posts.php"><i class="fa-solid fa-newspaper"></i> Posts</a></li>
                    <li><a href="admin-add-post.php" class="<?php echo !$isEdit ? 'active' : ''; ?>"><i class="fa-solid fa-plus"></i> Add New Post</a></li>
                    <li><a href="admin-comments.php"><i class="fa-solid fa-comments"></i> Comments</a></li>
                    <li><a href="admin-subscribers.php"><i class="fa-solid fa-users"></i> Subscribers</a></li>
                    <li class="nav-divider"></li>
                    <li><a href="blog.php" target="_blank"><i class="fa-solid fa-external-link-alt"></i> View Blog</a></li>
                    <li><a href="admin-logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><?php echo $isEdit ? 'Edit Post' : 'Add New Post'; ?></h1>
                <div class="admin-actions">
                    <a href="admin-posts.php" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Posts
                    </a>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="admin-message error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
                </div>
            <?php endif; ?>
            
            <!-- Post Form -->
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="form-grid">
                    <div class="form-main">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo $isEdit ? htmlspecialchars($post['title']) : ''; ?>"
                                   placeholder="Enter post title">
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea id="content" name="content" rows="20" required 
                                      placeholder="Write your post content here..."><?php echo $isEdit ? htmlspecialchars($post['content']) : ''; ?></textarea>
                            <div class="form-help">You can use HTML tags for formatting.</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="excerpt">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3" 
                                      placeholder="Brief description of the post (auto-generated if left empty)"><?php echo $isEdit ? htmlspecialchars($post['excerpt']) : ''; ?></textarea>
                            <div class="form-help">Used in post previews and meta descriptions.</div>
                        </div>
                    </div>
                    
                    <div class="form-sidebar">
                        <div class="form-section">
                            <h3>Publish</h3>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_published" 
                                           <?php echo ($isEdit && $post['is_published'] === 't') || (!$isEdit) ? 'checked' : ''; ?>>
                                    Publish immediately
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_featured" 
                                           <?php echo ($isEdit && $post['is_featured'] === 't') ? 'checked' : ''; ?>>
                                    Featured post
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Post Details</h3>
                            
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" required>
                                    <option value="">Select category</option>
                                    <?php foreach ($BLOG_CATEGORIES as $key => $name): ?>
                                        <option value="<?php echo $key; ?>" 
                                                <?php echo ($isEdit && $post['category'] === $key) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="read_time">Reading Time (minutes)</label>
                                <input type="number" id="read_time" name="read_time" min="1" max="120"
                                       value="<?php echo $isEdit ? $post['read_time'] : '5'; ?>"
                                       placeholder="5">
                                <div class="form-help">Leave empty to auto-calculate.</div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Featured Image</h3>
                            
                            <?php if ($isEdit && !empty($post['featured_image'])): ?>
                                <div class="current-image">
                                    <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" 
                                         alt="Current featured image" style="max-width: 100%; height: auto;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="featured_image">
                                    <?php echo ($isEdit && !empty($post['featured_image'])) ? 'Replace Image' : 'Upload Image'; ?>
                                </label>
                                <input type="file" id="featured_image" name="featured_image" 
                                       accept="image/*">
                                <div class="form-help">JPG, PNG, GIF, WebP. Max 5MB.</div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Author Info</h3>
                            
                            <div class="form-group">
                                <label for="author_name">Author Name *</label>
                                <input type="text" id="author_name" name="author_name" required 
                                       value="<?php echo $isEdit ? htmlspecialchars($post['author_name']) : ''; ?>"
                                       placeholder="John Smith">
                            </div>
                            
                            <div class="form-group">
                                <label for="author_title">Author Title</label>
                                <input type="text" id="author_title" name="author_title" 
                                       value="<?php echo $isEdit ? htmlspecialchars($post['author_title']) : ''; ?>"
                                       placeholder="Lead Developer">
                            </div>
                            
                            <?php if ($isEdit && !empty($post['author_avatar'])): ?>
                                <div class="current-avatar">
                                    <img src="<?php echo UPLOAD_URL . $post['author_avatar']; ?>" 
                                         alt="Current author avatar" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="author_avatar">
                                    <?php echo ($isEdit && !empty($post['author_avatar'])) ? 'Replace Avatar' : 'Author Avatar'; ?>
                                </label>
                                <input type="file" id="author_avatar" name="author_avatar" 
                                       accept="image/*">
                                <div class="form-help">Square image recommended.</div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>SEO Settings</h3>
                            
                            <div class="form-group">
                                <label for="meta_title">Meta Title</label>
                                <input type="text" id="meta_title" name="meta_title" maxlength="60"
                                       value="<?php echo $isEdit ? htmlspecialchars($post['meta_title']) : ''; ?>"
                                       placeholder="Auto-generated if empty">
                                <div class="form-help">Recommended: 50-60 characters.</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" rows="3" maxlength="160"
                                          placeholder="Auto-generated if empty"><?php echo $isEdit ? htmlspecialchars($post['meta_description']) : ''; ?></textarea>
                                <div class="form-help">Recommended: 150-160 characters.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-save"></i>
                        <?php echo $isEdit ? 'Update Post' : 'Create Post'; ?>
                    </button>
                    
                    <a href="admin-posts.php" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-resize content textarea
            const contentTextarea = document.getElementById('content');
            if (contentTextarea) {
                contentTextarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
            
            // Character counters for SEO fields
            const metaTitle = document.getElementById('meta_title');
            const metaDescription = document.getElementById('meta_description');
            
            function addCharCounter(input, maxLength) {
                const counter = document.createElement('div');
                counter.className = 'char-counter';
                counter.style.fontSize = '0.8rem';
                counter.style.color = '#6b7280';
                counter.style.marginTop = '0.25rem';
                
                function updateCounter() {
                    const length = input.value.length;
                    counter.textContent = `${length}/${maxLength} characters`;
                    counter.style.color = length > maxLength ? '#ef4444' : '#6b7280';
                }
                
                input.addEventListener('input', updateCounter);
                input.parentNode.appendChild(counter);
                updateCounter();
            }
            
            if (metaTitle) addCharCounter(metaTitle, 60);
            if (metaDescription) addCharCounter(metaDescription, 160);
        });
    </script>

    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .form-main {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .form-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .form-section h3 {
            margin: 0 0 1rem 0;
            color: var(--heading-color);
            font-size: 1.1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .current-image,
        .current-avatar {
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            text-align: center;
        }
        
        .current-image img {
            border-radius: 8px;
        }
        
        .form-actions {
            margin-top: 2rem;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
