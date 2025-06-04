<?php
/**
 * Blog Model Class
 * Handles all blog-related database operations
 */

require_once 'database.php';

class BlogModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get published posts with pagination and filtering
     */
    public function getPosts($page = 1, $category = null, $limit = POSTS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $where = "WHERE p.is_published = true";
        $params = [];
        
        if ($category && $category !== 'all') {
            $where .= " AND p.category = :category";
            $params['category'] = $category;
        }
        
        $sql = "
            SELECT p.id, p.title, p.slug, p.excerpt, p.category, p.featured_image, p.author_name, 
                   p.author_title, p.author_avatar, p.read_time, p.published_at,
                   COALESCE(DATE_PART('epoch', p.published_at), DATE_PART('epoch', p.created_at)) as published_timestamp,
                   COUNT(c.id) as comment_count
            FROM posts p
            LEFT JOIN comments c ON p.id = c.post_id AND c.is_approved = true
            $where 
            GROUP BY p.id, p.title, p.slug, p.excerpt, p.category, p.featured_image, 
                     p.author_name, p.author_title, p.author_avatar, p.read_time, p.published_at, p.created_at
            ORDER BY COALESCE(p.published_at, p.created_at) DESC 
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get total count of published posts
     */
    public function getPostsCount($category = null) {
        $where = "WHERE is_published = true";
        $params = [];
        
        if ($category && $category !== 'all') {
            $where .= " AND category = :category";
            $params['category'] = $category;
        }
        
        $sql = "SELECT COUNT(*) FROM posts $where";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    /**
     * Get single post by slug
     */
    public function getPostBySlug($slug) {
        $sql = "
            SELECT id, title, slug, content, excerpt, category, featured_image, 
                   author_name, author_title, author_avatar, read_time, 
                   meta_title, meta_description, published_at, created_at,
                   COALESCE(DATE_PART('epoch', published_at), DATE_PART('epoch', created_at)) as published_timestamp
            FROM posts 
            WHERE slug = :slug AND is_published = true
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }
    
    /**
     * Get featured post
     */
    public function getFeaturedPost() {
        $sql = "
            SELECT p.id, p.title, p.slug, p.excerpt, p.category, p.featured_image, 
                   p.author_name, p.author_title, p.author_avatar, p.read_time, p.published_at,
                   COALESCE(DATE_PART('epoch', p.published_at), DATE_PART('epoch', p.created_at)) as published_timestamp,
                   COUNT(c.id) as comment_count
            FROM posts p
            LEFT JOIN comments c ON p.id = c.post_id AND c.is_approved = true
            WHERE p.is_featured = true AND p.is_published = true 
            GROUP BY p.id, p.title, p.slug, p.excerpt, p.category, p.featured_image, 
                     p.author_name, p.author_title, p.author_avatar, p.read_time, p.published_at, p.created_at
            ORDER BY COALESCE(p.published_at, p.created_at) DESC 
            LIMIT 1
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    /**
     * Get recent posts excluding current post
     */
    public function getRecentPosts($excludeId = null, $limit = 3) {
        $where = "WHERE p.is_published = TRUE";
        $params = [];
        
        if ($excludeId) {
            $where .= " AND p.id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $sql = "
            SELECT p.id, p.title, p.slug, p.excerpt, p.category, p.featured_image, 
                   p.author_name, p.read_time, p.published_at,
                   COALESCE(DATE_PART('epoch', p.published_at), DATE_PART('epoch', p.created_at)) as published_timestamp,
                   COUNT(c.id) as comment_count
            FROM posts p
            LEFT JOIN comments c ON p.id = c.post_id AND c.is_approved = true
            $where 
            GROUP BY p.id, p.title, p.slug, p.excerpt, p.category, p.featured_image, 
                     p.author_name, p.read_time, p.published_at, p.created_at
            ORDER BY COALESCE(p.published_at, p.created_at) DESC 
            LIMIT :limit
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Search posts
     */
    public function searchPosts($query, $page = 1, $limit = POSTS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT id, title, slug, excerpt, category, featured_image, 
                   author_name, author_title, author_avatar, read_time, published_at,
                   DATE_PART('epoch', published_at) as published_timestamp
            FROM posts 
            WHERE is_published = true 
            AND (title ILIKE :query OR content ILIKE :query OR excerpt ILIKE :query)
            ORDER BY published_at DESC 
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%$query%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all categories with post counts
     */
    public function getCategoriesWithCounts() {
        $sql = "
            SELECT category, COUNT(*) as post_count 
            FROM posts 
            WHERE is_published = true 
            GROUP BY category 
            ORDER BY post_count DESC
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Subscribe email to newsletter
     */
    public function subscribeNewsletter($email) {
        try {
            $sql = "
                INSERT INTO newsletter_subscribers (email) 
                VALUES (:email) 
                ON CONFLICT (email) 
                DO UPDATE SET is_active = true, unsubscribed_at = NULL
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Add comment to post
     */
    public function addComment($postId, $name, $email, $website, $content) {
        try {
            $sql = "
                INSERT INTO comments (post_id, name, email, website, content, created_at) 
                VALUES (:post_id, :name, :email, :website, :content, CURRENT_TIMESTAMP)
                RETURNING id
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'post_id' => $postId,
                'name' => $name,
                'email' => $email,
                'website' => $website,
                'content' => $content
            ]);
            
            if ($result) {
                return $stmt->fetchColumn();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Error adding comment: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get approved comments for a post
     */
    public function getComments($postId) {
        $sql = "
            SELECT name, email, website, content, created_at 
            FROM comments 
            WHERE post_id = :post_id AND is_approved = true 
            ORDER BY created_at ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }
    
    // ===================
    // ADMIN FUNCTIONS
    // ===================
    
    /**
     * Get all posts for admin (including unpublished)
     */
    public function getAllPosts($page = 1, $limit = ADMIN_POSTS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT id, title, slug, category, is_published, is_featured, 
                   author_name, created_at, updated_at, published_at
            FROM posts 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get post for editing
     */
    public function getPostForEdit($id) {
        $sql = "SELECT * FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new post
     */
    public function createPost($data) {
        $sql = "
            INSERT INTO posts (title, slug, content, excerpt, category, featured_image, 
                             is_featured, is_published, author_name, author_title, author_avatar, 
                             read_time, meta_title, meta_description, published_at)
            VALUES (:title, :slug, :content, :excerpt, :category, :featured_image,
                    :is_featured, :is_published, :author_name, :author_title, :author_avatar,
                    :read_time, :meta_title, :meta_description, :published_at)
            RETURNING id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $stmt->fetchColumn();
    }
    
    /**
     * Update existing post
     */
    public function updatePost($id, $data) {
        $data['id'] = $id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $sql = "
            UPDATE posts SET 
                title = :title, slug = :slug, content = :content, excerpt = :excerpt,
                category = :category, featured_image = :featured_image, is_featured = :is_featured,
                is_published = :is_published, author_name = :author_name, author_title = :author_title,
                author_avatar = :author_avatar, read_time = :read_time, meta_title = :meta_title,
                meta_description = :meta_description, published_at = :published_at, updated_at = :updated_at
            WHERE id = :id
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Delete post
     */
    public function deletePost($id) {
        $sql = "DELETE FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Generate unique slug
     */
    public function generateUniqueSlug($title, $excludeId = null) {
        $slug = $this->createSlug($title);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        $where = "WHERE slug = :slug";
        $params = ['slug' => $slug];
        
        if ($excludeId) {
            $where .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $sql = "SELECT COUNT(*) FROM posts $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Create URL-friendly slug from title
     */
    private function createSlug($text) {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return substr($slug, 0, 100); // Limit length
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];
        
        // Total posts
        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        $stats['total_posts'] = $stmt->fetchColumn();
        
        // Published posts
        $stmt = $this->db->query("SELECT COUNT(*) FROM posts WHERE is_published = true");
        $stats['published_posts'] = $stmt->fetchColumn();
        
        // Draft posts
        $stats['draft_posts'] = $stats['total_posts'] - $stats['published_posts'];
        
        // Total comments
        $stmt = $this->db->query("SELECT COUNT(*) FROM comments");
        $stats['total_comments'] = $stmt->fetchColumn();
        
        // Pending comments
        $stmt = $this->db->query("SELECT COUNT(*) FROM comments WHERE is_approved = false");
        $stats['pending_comments'] = $stmt->fetchColumn();
        
        // Newsletter subscribers
        $stmt = $this->db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = true");
        $stats['newsletter_subscribers'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get recent comments for admin
     */
    public function getRecentComments($limit = 5) {
        $sql = "
            SELECT c.*, p.title as post_title, p.slug as post_slug
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            ORDER BY c.created_at DESC
            LIMIT :limit
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Approve comment
     */
    public function approveComment($id) {
        $sql = "UPDATE comments SET is_approved = true WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Delete comment
     */
    public function deleteComment($id) {
        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Get newsletter subscribers
     */
    public function getNewsletterSubscribers($page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT email, subscribed_at, is_active
            FROM newsletter_subscribers
            ORDER BY subscribed_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
