<?php
/**
 * Database Connection and Management Class
 */

require_once 'config.php';

class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Initialize database tables
     */
    public function initializeTables() {
        $sql = "
        -- Create posts table
        CREATE TABLE IF NOT EXISTS posts (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content TEXT NOT NULL,
            excerpt TEXT,
            category VARCHAR(50) NOT NULL,
            featured_image VARCHAR(255),
            is_featured BOOLEAN DEFAULT FALSE,
            is_published BOOLEAN DEFAULT FALSE,
            author_name VARCHAR(100) NOT NULL,
            author_title VARCHAR(100),
            author_avatar VARCHAR(255),
            read_time INTEGER DEFAULT 5,
            meta_title VARCHAR(255),
            meta_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            published_at TIMESTAMP NULL
        );
        
        -- Create tags table
        CREATE TABLE IF NOT EXISTS tags (
            id SERIAL PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            slug VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Create post_tags junction table
        CREATE TABLE IF NOT EXISTS post_tags (
            post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
            tag_id INTEGER REFERENCES tags(id) ON DELETE CASCADE,
            PRIMARY KEY (post_id, tag_id)
        );
        
        -- Create comments table
        CREATE TABLE IF NOT EXISTS comments (
            id SERIAL PRIMARY KEY,
            post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            website VARCHAR(255),
            content TEXT NOT NULL,
            is_approved BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Create newsletter_subscribers table
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at TIMESTAMP NULL
        );
        
        -- Create indexes for better performance
        CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);
        CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category);
        CREATE INDEX IF NOT EXISTS idx_posts_published ON posts(is_published);
        CREATE INDEX IF NOT EXISTS idx_posts_featured ON posts(is_featured);
        CREATE INDEX IF NOT EXISTS idx_posts_published_at ON posts(published_at);
        CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id);
        CREATE INDEX IF NOT EXISTS idx_comments_approved ON comments(is_approved);
        ";
        
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Database initialization failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Insert sample data for testing
     */
    public function insertSampleData() {
        // Check if posts already exist
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM posts");
        if ($stmt->fetchColumn() > 0) {
            return; // Data already exists
        }
        
        $samplePosts = [
            [
                'title' => 'Complete Guide to Web Scraping with Selenix',
                'slug' => 'complete-guide-web-scraping-selenix',
                'content' => '<p>Learn how to extract data from any website using Selenix\'s powerful automation tools. This comprehensive guide covers everything from basic data extraction to handling complex dynamic websites.</p>
                
                <h2>Getting Started with Web Scraping</h2>
                <p>Web scraping is the process of automatically extracting data from websites. With Selenix, you can scrape data without writing any code.</p>
                
                <h2>Setting Up Your First Scraper</h2>
                <p>Follow these steps to create your first web scraper:</p>
                <ol>
                    <li>Open Selenix and navigate to the target website</li>
                    <li>Click on the elements you want to extract</li>
                    <li>Configure the data extraction settings</li>
                    <li>Run your scraper and export the data</li>
                </ol>
                
                <h2>Advanced Techniques</h2>
                <p>For more complex websites, you can use advanced features like:</p>
                <ul>
                    <li>Dynamic element detection</li>
                    <li>JavaScript execution</li>
                    <li>API integration</li>
                    <li>Scheduled automation</li>
                </ul>',
                'excerpt' => 'Learn how to extract data from any website using Selenix\'s powerful automation tools. This comprehensive guide covers everything from basic data extraction to handling complex dynamic websites.',
                'category' => 'tutorials',
                'featured_image' => 'web-scraping-guide.jpg',
                'is_featured' => 't',
                'is_published' => 't',
                'author_name' => 'John Smith',
                'author_title' => 'Lead Developer',
                'author_avatar' => 'john-smith.jpg',
                'read_time' => 12,
                'meta_title' => 'Complete Web Scraping Guide with Selenix - Extract Data Easily',
                'meta_description' => 'Master web scraping with our comprehensive Selenix guide. Learn to extract data from any website without coding. Step-by-step tutorial included.',
                'published_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'title' => 'Introducing AI-Powered Element Detection',
                'slug' => 'ai-powered-element-detection',
                'content' => '<p>Our latest update brings machine learning to element selection, making your automations more reliable than ever before.</p>
                
                <h2>What is AI-Powered Element Detection?</h2>
                <p>This new feature uses advanced machine learning algorithms to intelligently identify and select web elements, even when they change between page loads.</p>
                
                <h2>Key Benefits</h2>
                <ul>
                    <li>95% more reliable element selection</li>
                    <li>Automatic adaptation to layout changes</li>
                    <li>Reduced maintenance overhead</li>
                    <li>Smart fallback mechanisms</li>
                </ul>
                
                <h2>How to Enable</h2>
                <p>The AI-powered detection is enabled by default in all new automations. For existing automations, you can enable it in the settings panel.</p>',
                'excerpt' => 'Our latest update brings machine learning to element selection, making your automations more reliable than ever.',
                'category' => 'features',
                'featured_image' => 'ai-element-detection.jpg',
                'is_featured' => 'f',
                'is_published' => 't',
                'author_name' => 'Sarah Evans',
                'author_title' => 'AI Research Lead',
                'author_avatar' => 'sarah-evans.jpg',
                'read_time' => 6,
                'meta_title' => 'AI-Powered Element Detection - Selenix New Feature',
                'meta_description' => 'Discover how AI-powered element detection makes your Selenix automations 95% more reliable. Machine learning for smarter web automation.',
                'published_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'title' => 'How TechCorp Saved 40 Hours Weekly with Selenix',
                'slug' => 'techcorp-case-study-40-hours-weekly',
                'content' => '<p>Discover how a growing e-commerce company transformed their workflow using Selenix automation templates.</p>
                
                <h2>The Challenge</h2>
                <p>TechCorp was spending over 40 hours per week on manual data entry tasks across multiple e-commerce platforms. Their team was overwhelmed with repetitive work that prevented them from focusing on strategic initiatives.</p>
                
                <h2>The Solution</h2>
                <p>By implementing Selenix automation templates, TechCorp was able to:</p>
                <ul>
                    <li>Automate product listings across 5 platforms</li>
                    <li>Streamline inventory management</li>
                    <li>Automatically update pricing information</li>
                    <li>Generate weekly performance reports</li>
                </ul>
                
                <h2>The Results</h2>
                <blockquote>
                    "Selenix has completely transformed how we operate. What used to take our team 40 hours now takes just 2 hours of setup time. We\'ve redirected that saved time toward customer service and business development."
                </blockquote>
                <p><em>- Michael Rodriguez, CTO at TechCorp</em></p>
                
                <h2>Key Metrics</h2>
                <ul>
                    <li>95% reduction in manual data entry time</li>
                    <li>99.8% accuracy in automated processes</li>
                    <li>$50,000 annual cost savings</li>
                    <li>ROI achieved within the first month</li>
                </ul>',
                'excerpt' => 'Discover how a growing e-commerce company transformed their workflow using Selenix automation templates.',
                'category' => 'case-studies',
                'featured_image' => 'techcorp-case-study.jpg',
                'is_featured' => 'f',
                'is_published' => 't',
                'author_name' => 'Mike Johnson',
                'author_title' => 'Customer Success Manager',
                'author_avatar' => 'mike-johnson.jpg',
                'read_time' => 8,
                'meta_title' => 'TechCorp Case Study: 40 Hours Weekly Saved with Selenix Automation',
                'meta_description' => 'Learn how TechCorp saved 40+ hours weekly using Selenix automation. Real results, metrics, and ROI from e-commerce automation implementation.',
                'published_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
            ],
            [
                'title' => '10 Pro Tips for Faster Web Automation',
                'slug' => '10-pro-tips-faster-web-automation',
                'content' => '<p>Boost your automation performance with these expert tips and best practices from our power users.</p>
                
                <h2>1. Use Smart Waits Instead of Fixed Delays</h2>
                <p>Instead of adding fixed 5-second delays, use intelligent waiting conditions that adapt to page load times.</p>
                
                <h2>2. Leverage CSS Selectors for Stability</h2>
                <p>CSS selectors are often more stable than XPath selectors for element identification.</p>
                
                <h2>3. Implement Error Handling</h2>
                <p>Always add fallback actions for when elements aren\'t found or pages don\'t load as expected.</p>
                
                <h2>4. Use Variables for Dynamic Content</h2>
                <p>Store frequently changing data in variables to make your automations more flexible.</p>
                
                <h2>5. Test on Different Screen Sizes</h2>
                <p>Ensure your automations work on both desktop and mobile viewport sizes.</p>
                
                <h2>6. Optimize Loop Performance</h2>
                <p>When processing large datasets, break them into smaller chunks to prevent timeouts.</p>
                
                <h2>7. Use Headless Mode for Background Tasks</h2>
                <p>Running in headless mode can significantly speed up automation execution.</p>
                
                <h2>8. Cache Frequently Used Data</h2>
                <p>Store commonly accessed information locally to reduce API calls and page loads.</p>
                
                <h2>9. Monitor Performance Metrics</h2>
                <p>Track execution times and success rates to identify optimization opportunities.</p>
                
                <h2>10. Document Your Automations</h2>
                <p>Well-documented automations are easier to maintain and troubleshoot.</p>',
                'excerpt' => 'Boost your automation performance with these expert tips and best practices from our power users.',
                'category' => 'automation',
                'featured_image' => 'pro-tips-automation.jpg',
                'is_featured' => 'f',
                'is_published' => 't',
                'author_name' => 'Anna Lee',
                'author_title' => 'Automation Specialist',
                'author_avatar' => 'anna-lee.jpg',
                'read_time' => 7,
                'meta_title' => '10 Pro Tips for Faster Web Automation - Selenix Expert Guide',
                'meta_description' => 'Master web automation with 10 expert tips for faster, more reliable automations. Performance optimization strategies from Selenix power users.',
                'published_at' => date('Y-m-d H:i:s', strtotime('-2 weeks'))
            ]
        ];
        
        foreach ($samplePosts as $post) {
            $stmt = $this->pdo->prepare("
                INSERT INTO posts (title, slug, content, excerpt, category, featured_image, is_featured, is_published, 
                                 author_name, author_title, author_avatar, read_time, meta_title, meta_description, published_at)
                VALUES (:title, :slug, :content, :excerpt, :category, :featured_image, :is_featured, :is_published,
                        :author_name, :author_title, :author_avatar, :read_time, :meta_title, :meta_description, :published_at)
            ");
            $stmt->execute($post);
        }
        
        // Insert sample tags
        $sampleTags = [
            ['name' => 'Web Scraping', 'slug' => 'web-scraping'],
            ['name' => 'Automation', 'slug' => 'automation'],
            ['name' => 'AI', 'slug' => 'ai'],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning'],
            ['name' => 'Case Study', 'slug' => 'case-study'],
            ['name' => 'Tips', 'slug' => 'tips'],
            ['name' => 'Performance', 'slug' => 'performance']
        ];
        
        foreach ($sampleTags as $tag) {
            $stmt = $this->pdo->prepare("INSERT INTO tags (name, slug) VALUES (:name, :slug)");
            $stmt->execute($tag);
        }
    }
}
?>
