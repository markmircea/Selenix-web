<?php
require_once 'config.php';
require_once 'models.php';
require_once 'functions.php';
require_once 'ai-service.php';

requireAdmin();

$blogModel = new BlogModel();
$error = '';
$success = '';
$generatedArticle = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'generate') {
            $topic = sanitizeInput($_POST['topic']);
            $category = sanitizeInput($_POST['category']);
            $targetWords = intval($_POST['target_words']);
            // Use hardcoded API key
            $apiKey = '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';
            
            if (empty($topic)) {
                $error = 'Please enter a topic for the article';
            } else {
                try {
                    $aiService = new AIService($apiKey);
                    $generatedArticle = $aiService->generateArticle($topic, $category, $targetWords);
                    $success = 'Article generated successfully! Review and edit before saving.';
                } catch (Exception $e) {
                    $error = 'Error generating article: ' . $e->getMessage();
                    error_log('AI Generation Error: ' . $e->getMessage());
                }
            }
        } elseif ($_POST['action'] === 'save') {
            // Save generated article as post
            $title = trim($_POST['title']);
            // DO NOT sanitize HTML content - we want to preserve HTML formatting
            $content = $_POST['content'];
            $excerpt = sanitizeInput($_POST['excerpt']);
            $category = sanitizeInput($_POST['category']);
            $readTime = intval($_POST['read_time']);
            $authorName = sanitizeInput($_POST['author_name']) ?: 'Selenix Team';
            $authorTitle = sanitizeInput($_POST['author_title']) ?: 'Automation Experts';
            
            if (empty($title) || empty($content)) {
                $error = 'Title and content are required';
            } else {
                $slug = $blogModel->generateUniqueSlug($title);
                
                if (empty($excerpt)) {
                    $excerpt = generateExcerpt($content);
                }
                
                $postData = [
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content, // Store content as-is with HTML
                    'excerpt' => $excerpt,
                    'category' => $category,
                    'featured_image' => '',
                    'is_featured' => 'f',
                    'is_published' => 'f', // Save as draft initially
                    'author_name' => $authorName,
                    'author_title' => $authorTitle,
                    'author_avatar' => '',
                    'read_time' => $readTime ?: estimateReadingTime($content),
                    'meta_title' => $title . ' - ' . BLOG_TITLE,
                    'meta_description' => $excerpt,
                    'published_at' => null
                ];
                
                $newPostId = $blogModel->createPost($postData);
                if ($newPostId) {
                    header('Location: admin-edit-post.php?id=' . $newPostId . '&message=ai_generated');
                    exit;
                } else {
                    $error = 'Error saving article to database';
                }
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
    <title>AI Article Generator - Selenix Blog Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="blog-styles.css">
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="ai-generator.css">
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
                    <li><a href="admin-add-post.php"><i class="fa-solid fa-plus"></i> Add New Post</a></li>
                    <li><a href="admin-ai-generate.php" class="active"><i class="fa-solid fa-brain"></i> AI Generator</a></li>
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
                <h1><i class="fa-solid fa-brain"></i> AI Article Generator</h1>
                <div class="admin-actions">
                    <a href="admin-posts.php" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Posts
                    </a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="admin-message error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="admin-message success">
                    <i class="fa-solid fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$generatedArticle): ?>
            <!-- Generation Form -->
            <div class="ai-generator-section">
                <div class="ai-info-card">
                    <h3><i class="fa-solid fa-robot"></i> AI-Powered Content Creation</h3>
                    <p>Generate engaging blog articles instantly with our integrated AI system. Create practical, actionable content focused on real-world automation insights and business value with just a topic description.</p>
                    
                    <div class="ai-features">
                        <div class="ai-feature">
                            <i class="fa-solid fa-lightbulb"></i>
                            <span>Practical Insights</span>
                        </div>
                        <div class="ai-feature">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Business Value</span>
                        </div>
                        <div class="ai-feature">
                            <i class="fa-solid fa-users"></i>
                            <span>Audience-Focused</span>
                        </div>
                        <div class="ai-feature">
                            <i class="fa-solid fa-search"></i>
                            <span>SEO Optimized</span>
                        </div>
                    </div>
                </div>
                
                <form method="POST" class="ai-form">
                    <input type="hidden" name="action" value="generate">
                    
                    <div class="form-group">
                        <label for="topic">
                            <i class="fa-solid fa-lightbulb"></i>
                            Article Topic *
                        </label>
                        <input type="text" id="topic" name="topic" required 
                               placeholder="e.g., 'How small businesses can save 10+ hours weekly with automation'">
                        <div class="form-help">Be specific about the value proposition or problem you want to address. Focus on benefits rather than technical details.</div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">
                                <i class="fa-solid fa-folder"></i>
                                Category *
                            </label>
                            <select id="category" name="category" required>
                                <?php foreach ($BLOG_CATEGORIES as $key => $name): ?>
                                    <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="target_words">
                                <i class="fa-solid fa-ruler"></i>
                                Target Length
                            </label>
                            <select id="target_words" name="target_words">
                                <option value="1000">Quick Read (~1000 words, 4-5 min)</option>
                                <option value="1500" selected>Standard (~1500 words, 6-8 min)</option>
                                <option value="2000">In-Depth (~2000 words, 8-10 min)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg" id="generate-btn">
                            <i class="fa-solid fa-magic-wand-sparkles"></i>
                            Generate Article
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <!-- Generated Article Preview -->
            <div class="generated-article-section">
                <div class="article-preview">
                    <h3><i class="fa-solid fa-eye"></i> Generated Article Preview</h3>
                    
                    <div class="article-meta">
                        <span><i class="fa-solid fa-file-text"></i> <?php echo str_word_count(strip_tags($generatedArticle['content'])); ?> words</span>
                        <span><i class="fa-solid fa-clock"></i> <?php echo $generatedArticle['readTime'] ?? 5; ?> min read</span>
                        <span><i class="fa-solid fa-tag"></i> <?php echo ucfirst($_POST['category'] ?? 'tutorial'); ?></span>
                    </div>
                    
                    <div class="article-content-preview">
                        <h1><?php echo htmlspecialchars($generatedArticle['title']); ?></h1>
                        <p class="article-excerpt"><?php echo htmlspecialchars($generatedArticle['excerpt']); ?></p>
                        <div class="article-body">
                            <?php echo $generatedArticle['content']; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($generatedArticle['keyTakeaways'])): ?>
                    <div class="key-takeaways">
                        <h4><i class="fa-solid fa-star"></i> Key Takeaways</h4>
                        <ul class="takeaways-list">
                            <?php foreach ($generatedArticle['keyTakeaways'] as $takeaway): ?>
                                <li><?php echo htmlspecialchars($takeaway); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="save-article-form">
                    <h3><i class="fa-solid fa-save"></i> Save Article</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="save">
                        <!-- Store the raw HTML content without escaping -->
                        <textarea name="content" style="display: none;"><?php echo htmlspecialchars($generatedArticle['content']); ?></textarea>
                        <input type="hidden" name="read_time" value="<?php echo $generatedArticle['readTime'] ?? 5; ?>">
                        
                        <div class="form-group">
                            <label for="save_title">Title</label>
                            <input type="text" id="save_title" name="title" 
                                   value="<?php echo htmlspecialchars($generatedArticle['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="save_excerpt">Excerpt</label>
                            <textarea id="save_excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($generatedArticle['excerpt']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="save_category">Category</label>
                            <select id="save_category" name="category" required>
                                <?php foreach ($BLOG_CATEGORIES as $key => $name): ?>
                                    <option value="<?php echo $key; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] === $key) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="author_name">Author Name</label>
                            <input type="text" id="author_name" name="author_name" value="Selenix Team">
                        </div>
                        
                        <div class="form-group">
                            <label for="author_title">Author Title</label>
                            <input type="text" id="author_title" name="author_title" value="Automation Experts">
                        </div>
                        
                        <?php if (!empty($generatedArticle['suggestedTags'])): ?>
                        <div class="suggested-tags">
                            <label>Suggested Tags</label>
                            <div class="tags-list">
                                <?php foreach ($generatedArticle['suggestedTags'] as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-save"></i>
                                Save as Draft
                            </button>
                            
                            <a href="admin-ai-generate.php" class="btn btn-secondary btn-lg">
                                <i class="fa-solid fa-arrow-left"></i>
                                Generate New
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateForm = document.querySelector('.ai-form');
            const generateBtn = document.getElementById('generate-btn');
            
            if (generateForm && generateBtn) {
                generateForm.addEventListener('submit', function() {
                    generateBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
                    generateBtn.disabled = true;
                });
            }
            
            // Auto-resize textareas
            document.querySelectorAll('textarea').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });
            
            // Preview scroll sync for long articles
            const articlePreview = document.querySelector('.article-preview');
            if (articlePreview) {
                // Add smooth scrolling
                articlePreview.style.scrollBehavior = 'smooth';
            }
        });
    </script>
</body>
</html>