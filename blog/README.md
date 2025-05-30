# Selenix Blog System - Complete Setup Guide

A comprehensive blog management system built with PHP and PostgreSQL for the Selenix website.

## 🚀 Features

### Frontend
- **Dynamic Blog**: Category-based posts with pagination and filtering
- **Featured Posts**: Highlight important content on the blog homepage
- **Comment System**: Reader engagement with admin moderation
- **Newsletter Signup**: Built-in subscription management
- **SEO Optimized**: Meta tags, structured data, and clean URLs
- **Responsive Design**: Mobile-friendly design matching Selenix branding
- **Social Sharing**: Twitter, LinkedIn, Facebook, and email sharing

### Admin Panel
- **Dashboard**: Overview with statistics and recent activity
- **Post Management**: Create, edit, publish, and delete blog posts
- **Rich Editor**: HTML content editing with image uploads
- **Comment Moderation**: Approve, delete, and manage comments
- **Subscriber Management**: View and export newsletter subscribers
- **File Upload**: Featured images and author avatars
- **SEO Tools**: Meta titles, descriptions, and auto-generation

## 📋 Prerequisites

- **PHP 7.4+** with extensions:
  - PDO
  - pdo_pgsql
  - GD (for image handling)
  - fileinfo
- **PostgreSQL 12+**
- **Web Server** (Apache/Nginx)

## 🛠️ Installation

### 1. Database Setup

Create your PostgreSQL database and user:

```sql
CREATE DATABASE aibrainl_selenixblog;
CREATE USER aibrainl_selenix WITH PASSWORD 'She-wolf11';
GRANT ALL PRIVILEGES ON DATABASE aibrainl_selenixblog TO aibrainl_selenix;
```

### 2. Configuration

Edit `blog/config.php` with your settings:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'aibrainl_selenixblog');
define('DB_USER', 'aibrainl_selenix');
define('DB_PASS', 'She-wolf11');
define('DB_PORT', '5432');

// Site URLs (adjust for your domain)
define('SITE_URL', 'https://selenix.io');
define('BLOG_URL', SITE_URL . '/blog');
```

### 3. Initialize Database

Visit `/blog/setup.php` in your browser to:
- Create all database tables
- Insert sample blog posts
- Verify the setup

### 4. Admin Access

- **Login URL**: `/blog/admin-login.php`
- **Username**: `admin`
- **Password**: `selenix2025!`

⚠️ **Important**: Change the admin password in `config.php` for production!

## 📁 File Structure

```
blog/
├── config.php              # Configuration settings
├── database.php             # Database connection & setup
├── models.php               # Data access layer
├── functions.php            # Utility functions
├── blog.php                 # Main blog page
├── post.php                 # Individual post view
├── 404.php                  # Error page
├── blog-styles.css          # Blog-specific styling
├── admin-styles.css         # Admin panel styling
├── setup.php                # Database initialization
├── admin-login.php          # Admin authentication
├── admin-logout.php         # Admin logout
├── admin-dashboard.php      # Admin overview
├── admin-posts.php          # Post management
├── admin-add-post.php       # Create/edit posts
├── admin-edit-post.php      # Edit post redirect
├── admin-comments.php       # Comment moderation
├── admin-subscribers.php    # Newsletter management
└── uploads/                 # File upload directory
```

## 🎯 Usage Guide

### Creating Blog Posts

1. **Access Admin Panel**: Go to `/blog/admin-login.php`
2. **Add New Post**: Click "Add New Post" in the sidebar
3. **Fill Content**:
   - Title and content (HTML supported)
   - Category selection
   - Featured image upload
   - Author information
   - SEO settings
4. **Publish**: Check "Publish immediately" and save

### Managing Comments

1. **View Comments**: Go to "Comments" in admin sidebar
2. **Filter**: Use tabs to view All/Pending/Approved comments
3. **Moderate**: Approve or delete individual comments
4. **Bulk Actions**: Select multiple comments for batch operations

### Newsletter Management

1. **View Subscribers**: Go to "Subscribers" in admin sidebar
2. **Export Data**: Use "Export CSV" to download subscriber list
3. **Statistics**: View subscription trends and metrics
4. **Manage**: Delete inactive or unwanted subscribers

### Customization

#### Adding Categories

Edit `$BLOG_CATEGORIES` in `config.php`:

```php
$BLOG_CATEGORIES = [
    'tutorials' => 'Tutorials',
    'features' => 'Features',
    'case-studies' => 'Case Studies',
    'automation' => 'Automation Tips',
    'news' => 'News',          // Add new category
    'guides' => 'Guides'       // Add new category
];
```

#### Styling

- **Blog styles**: Edit `blog/blog-styles.css`
- **Admin styles**: Edit `blog/admin-styles.css`
- **Main styles**: Inherits from `../styles.css`

#### File Uploads

Configure upload settings in `config.php`:

```php
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
```

## 🔧 API Reference

### Blog Functions

```php
// Get published posts
$posts = $blogModel->getPosts($page, $category, $limit);

// Get single post
$post = $blogModel->getPostBySlug($slug);

// Get featured post
$featured = $blogModel->getFeaturedPost();

// Subscribe to newsletter
$result = $blogModel->subscribeNewsletter($email);
```

### Admin Functions

```php
// Create post
$postId = $blogModel->createPost($data);

// Update post
$result = $blogModel->updatePost($id, $data);

// Delete post
$result = $blogModel->deletePost($id);

// Manage comments
$result = $blogModel->approveComment($id);
$result = $blogModel->deleteComment($id);
```

## 🚨 Security Notes

### For Production:

1. **Change Admin Password**:
   ```php
   define('ADMIN_PASSWORD', password_hash('your_secure_password', PASSWORD_DEFAULT));
   ```

2. **Update Secret Key**:
   ```php
   define('SECRET_KEY', 'your_unique_secret_key_here');
   ```

3. **File Permissions**:
   ```bash
   chmod 755 blog/
   chmod 644 blog/*.php
   chmod 755 blog/uploads/
   ```

4. **Database Security**:
   - Use strong database passwords
   - Restrict database user permissions
   - Enable SSL connections

5. **Web Server**:
   - Configure proper SSL/TLS
   - Set up security headers
   - Restrict direct file access

## 🐛 Troubleshooting

### Common Issues:

**Database Connection Failed**
- Check PostgreSQL is running
- Verify credentials in `config.php`
- Ensure database exists

**File Upload Errors**
- Check `uploads/` directory permissions (755)
- Verify PHP upload limits
- Ensure GD extension is enabled

**Admin Login Issues**
- Clear browser cache/cookies
- Check session configuration
- Verify admin credentials

**Styling Issues**
- Clear browser cache
- Check CSS file paths
- Verify file permissions

### Debug Mode

Enable debug mode in `config.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📊 Database Schema

### Main Tables:

- **posts**: Blog post content and metadata
- **comments**: Reader comments with moderation
- **newsletter_subscribers**: Email subscription list
- **tags**: Post tagging system (extensible)
- **post_tags**: Many-to-many relationship

### Key Indexes:

- Post slugs, categories, publication status
- Comment approval status
- Subscriber activity status

## 🔄 Maintenance

### Regular Tasks:

1. **Backup Database**:
   ```bash
   pg_dump aibrainl_selenixblog > backup_$(date +%Y%m%d).sql
   ```

2. **Clean Uploads**:
   - Remove orphaned images
   - Optimize image sizes

3. **Monitor Comments**:
   - Review pending moderation
   - Check for spam patterns

4. **Update Content**:
   - Refresh old posts
   - Check broken links

## 📈 Performance Tips

1. **Database Optimization**:
   - Regular VACUUM and ANALYZE
   - Monitor query performance
   - Consider adding indexes for custom queries

2. **Caching**:
   - Implement Redis/Memcached for sessions
   - Add application-level caching
   - Use CDN for static assets

3. **Image Optimization**:
   - Compress uploaded images
   - Generate thumbnails
   - Use WebP format when possible

## 🤝 Contributing

To extend the blog system:

1. **Add New Features**: Follow existing code patterns
2. **Database Changes**: Update `database.php` initialization
3. **Styling**: Maintain design consistency
4. **Testing**: Test all CRUD operations

## 📞 Support

For issues or questions:

1. Check this documentation
2. Review error logs
3. Test with sample data
4. Verify configuration settings

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Compatible**: PHP 7.4+, PostgreSQL 12+
