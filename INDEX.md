# TuxCMS - Complete Architecture Index

A production-ready, headless Laravel CMS with comprehensive SEO support and 54+ RESTful API endpoints.

## Quick Navigation

### Documentation
1. **[SETUP_COMPLETE.md](SETUP_COMPLETE.md)** - Project completion summary
2. **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Full API reference with examples
3. **[ARCHITECTURE.md](ARCHITECTURE.md)** - Technical architecture & file structure

### Quick Start
```bash
# Install and setup
php artisan migrate:fresh --seed

# Start development server
php artisan serve

# Access API
curl http://localhost:8000/api/v1/health
```

## Project Statistics

- **7 Models** - Post, Category, Comment, Seo, Menu, MenuItem, Setting
- **11 Migrations** - Custom tables + Spatie packages
- **10 Controllers** - Full-featured API endpoints
- **9 Form Requests** - Input validation
- **10 Resources** - API response formatting
- **54+ Routes** - RESTful API endpoints
- **15 Permissions** - Role-based access control
- **4 Roles** - Admin, Editor, Author, Subscriber

## Core Features

### 1. Content Management
- **Posts & Pages** - Draft, published, scheduled, archived statuses
- **Categories** - Hierarchical with parent/child relationships
- **Comments** - Nested replies with moderation system
- **Auto Slugs** - Spatie sluggable integration
- **Tags** - Spatie tags integration
- **Media** - Upload and manage files with Spatie MediaLibrary

### 2. SEO System
- **Meta Tags** - Title, description, keywords
- **Open Graph** - Social media sharing (og:title, og:description, og:image, etc.)
- **Twitter Cards** - Twitter-specific optimization
- **Schema Markup** - JSON-LD structured data
- **Canonical URLs** - Duplicate prevention
- **Robots Directives** - index/follow control
- **XML Sitemap** - Auto-generated at /api/v1/sitemap.xml

### 3. API Features
- **Authentication** - Sanctum token-based auth
- **Pagination** - Configurable with metadata
- **Filtering** - Advanced QueryBuilder support
- **Sorting** - Multiple sort options
- **Includes** - Load relationships on demand
- **Full-Text Search** - Search across posts
- **Rate Limiting** - Ready for implementation

### 4. Security
- **Roles & Permissions** - 4 roles with 15 permissions
- **Route Protection** - Bearer token authentication
- **Request Validation** - Form request classes
- **SQL Injection Prevention** - Eloquent ORM
- **CORS Ready** - Configurable for frontend apps

## File Structure

```
app/
├── Models/                    (7 models)
├── Http/
│   ├── Controllers/Api/V1/   (10 controllers)
│   ├── Requests/             (9 form requests)
│   ├── Resources/            (10 resources)
│   └── Middleware/           (1 middleware)
├── Traits/                    (HasSeo trait)
└── Providers/                 (TuxCmsServiceProvider)

database/
├── migrations/               (8 custom + 3 from packages)
└── seeders/                  (2 custom seeders)

config/
└── tuxcms.php               (CMS configuration)

routes/
└── api.php                   (54+ API routes)
```

## API Endpoints by Category

### Authentication (4)
- Register, Login, Logout, Get Current User

### Posts (7)
- List, Show, Create, Update, Delete (with filtering & sorting)

### Categories (7)
- List, Tree, Show, Create, Update, Delete

### Comments (6)
- List, Show, Create, Update, Delete (with moderation)

### Menus (5)
- List, Show, Create, Update, Delete

### Settings (6)
- List, Get Grouped, Show, Create, Update, Delete

### SEO (3)
- Get Meta Tags, Get SEO, Update SEO

### Media (4)
- List, Show, Upload, Delete

### Utility (2)
- Search, Sitemap

## Database Schema Highlights

### Polymorphic Tables
- **seos** - Attach SEO metadata to any model
- **menu_items.linkable** - Link menu items to any content type

### Self-Referencing
- **categories.parent_id** - Support hierarchy
- **comments.parent_id** - Support nested replies
- **menu_items.parent_id** - Support nested items

### Enums
- **posts.status** - draft, published, scheduled, archived
- **posts.type** - post, page, custom
- **comments.status** - pending, approved, spam
- **menu_items.type** - custom, post, category, page

### Indexes
- Strategic indexes on frequently filtered columns
- Foreign key indexes for relationships
- Unique constraints on slugs and keys

## Configuration Options

### tuxcms.php Settings

```php
'api' => [
    'prefix' => 'api/v1',           // API prefix
    'pagination' => [
        'per_page' => 15,           // Default items per page
        'max_per_page' => 100,      // Maximum items per page
    ],
],
'posts' => [
    'per_page' => 15,               // Posts per page
    'enable_comments' => true,      // Allow comments
    'moderate_comments' => true,    // Require approval
    'auto_publish_scheduled' => true, // Auto-publish scheduled posts
],
'media' => [
    'max_file_size' => 10240,       // Max file size in KB
    'allowed_extensions' => [...],  // Allowed file types
    'collections' => [...],         // Media collections
],
'seo' => [
    'enable_sitemap' => true,       // Generate sitemap
    'enable_schema_markup' => true, // Add structured data
    'default_robots' => 'index, follow',
    'og_image_fallback' => null,    // Fallback OG image
    'twitter_card_type' => 'summary_large_image',
],
'cache' => [
    'enabled' => false,             // Enable caching
    'ttl' => 3600,                  // Cache TTL in seconds
],
```

## Package Dependencies

- **laravel/sanctum** - API authentication
- **spatie/laravel-sluggable** - Auto slug generation
- **spatie/laravel-medialibrary** - Media management
- **spatie/laravel-tags** - Tagging system
- **spatie/laravel-permission** - Roles & permissions
- **spatie/laravel-query-builder** - Advanced filtering
- **spatie/laravel-translatable** - Multi-language (ready)

## Getting Started Guide

### 1. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup
```bash
php artisan migrate:fresh --seed
```

### 3. Create Test User
```bash
php artisan tinker
>>> use App\Models\User;
>>> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password')])
```

### 4. Start Server
```bash
php artisan serve
```

### 5. Test API
```bash
# Health check
curl http://localhost:8000/api/v1/health

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Use token for protected endpoints
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/v1/posts
```

## Integration Examples

### With Next.js
```javascript
const response = await fetch('http://localhost:8000/api/v1/posts?published=true');
const { data, meta, links } = await response.json();
```

### With Vue.js
```javascript
const { data } = await axios.get('/api/v1/posts?include=author,categories,seo');
```

### With React
```javascript
const [posts, setPosts] = useState([]);
useEffect(() => {
  fetch('/api/v1/posts')
    .then(r => r.json())
    .then(({ data }) => setPosts(data));
}, []);
```

## Testing the API

### Get All Posts
```bash
curl http://localhost:8000/api/v1/posts
```

### Create Post (Protected)
```bash
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My First Post",
    "content": "Post content...",
    "excerpt": "Short excerpt",
    "status": "published",
    "type": "post"
  }'
```

### Search Posts
```bash
curl http://localhost:8000/api/v1/search?q=laravel
```

### Get SEO Tags
```bash
curl http://localhost:8000/api/v1/seo/meta-tags?slug=my-first-post&type=post
```

### Get Sitemap
```bash
curl http://localhost:8000/api/v1/sitemap.xml
```

## Roles & Permissions

### Admin Role
- All 15 permissions
- Full system access

### Editor Role
- Create/edit/publish posts
- Manage categories and comments
- Upload media
- Manage menus

### Author Role
- Create posts
- Edit own posts
- Create comments
- Upload media

### Subscriber Role
- View posts
- Create comments

## Production Deployment

### Pre-deployment Checklist
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] Database configured
- [ ] .env variables set
- [ ] Migrations run
- [ ] Media storage configured (S3)
- [ ] Cache configured (Redis)
- [ ] API rate limiting enabled
- [ ] Monitoring setup
- [ ] Backups configured

### Deployment Commands
```bash
php artisan migrate:fresh
php artisan db:seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Migrations not running
```bash
php artisan migrate:refresh
```

### Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Rebuild database
```bash
php artisan migrate:fresh --seed
```

### Check routes
```bash
php artisan route:list
```

## Support & Resources

- **Laravel Docs:** https://laravel.com/docs
- **Sanctum Docs:** https://laravel.com/docs/sanctum
- **Spatie Packages:** https://spatie.be/open-source
- **API Examples:** See API_DOCUMENTATION.md
- **Architecture:** See ARCHITECTURE.md

## License

TuxCMS is open source and available under the MIT license.

---

**Status:** ✅ Complete and Ready for Use

Created: March 13, 2024
Last Updated: March 13, 2024
