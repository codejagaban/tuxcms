# TuxCMS Architecture

Complete headless Laravel CMS with full SEO support.

## Project Structure

```
tuxcms/
├── app/
│   ├── Models/
│   │   ├── Post.php                 # Blog posts/pages model
│   │   ├── Category.php             # Category model with hierarchy
│   │   ├── Comment.php              # Comments with nested replies
│   │   ├── Seo.php                  # Polymorphic SEO metadata
│   │   ├── Menu.php                 # Menus model
│   │   ├── MenuItem.php             # Menu items with hierarchy
│   │   ├── Setting.php              # Key-value settings store
│   │   └── User.php                 # User model
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── PostController.php          # Posts CRUD + filtering
│   │   │   ├── CategoryController.php      # Categories CRUD + tree
│   │   │   ├── CommentController.php       # Comments management
│   │   │   ├── MenuController.php          # Menus CRUD + nested items
│   │   │   ├── SeoController.php           # SEO metadata management
│   │   │   ├── SettingController.php       # Settings CRUD
│   │   │   ├── MediaController.php         # Media upload/management
│   │   │   ├── AuthController.php          # Authentication (Sanctum)
│   │   │   ├── SearchController.php        # Full-text search
│   │   │   └── SitemapController.php       # XML sitemap generation
│   │   ├── Requests/
│   │   │   ├── StorePostRequest.php        # Post validation
│   │   │   ├── UpdatePostRequest.php
│   │   │   ├── StoreCategoryRequest.php    # Category validation
│   │   │   ├── UpdateCategoryRequest.php
│   │   │   ├── StoreCommentRequest.php     # Comment validation
│   │   │   ├── StoreMenuRequest.php        # Menu validation
│   │   │   ├── UpdateSettingRequest.php    # Setting validation
│   │   │   ├── LoginRequest.php            # Auth validation
│   │   │   └── RegisterRequest.php
│   │   ├── Resources/
│   │   │   ├── PostResource.php            # Post API response
│   │   │   ├── PostCollection.php          # Paginated posts response
│   │   │   ├── CategoryResource.php        # Category API response
│   │   │   ├── CommentResource.php         # Comment API response
│   │   │   ├── MenuResource.php            # Menu API response
│   │   │   ├── MenuItemResource.php        # MenuItem API response
│   │   │   ├── SeoResource.php             # SEO API response
│   │   │   ├── SettingResource.php         # Setting API response
│   │   │   ├── UserResource.php            # User API response
│   │   │   └── MediaResource.php           # Media API response
│   │   └── Middleware/
│   │       └── EnsureJsonResponse.php      # Force JSON responses
│   ├── Traits/
│   │   └── HasSeo.php                      # SEO trait for models
│   └── Providers/
│       └── TuxCmsServiceProvider.php        # Service provider
├── database/
│   ├── migrations/
│   │   ├── 2024_03_13_000001_create_posts_table.php
│   │   ├── 2024_03_13_000002_create_categories_table.php
│   │   ├── 2024_03_13_000003_create_post_category_table.php
│   │   ├── 2024_03_13_000004_create_comments_table.php
│   │   ├── 2024_03_13_000005_create_seos_table.php
│   │   ├── 2024_03_13_000006_create_menus_table.php
│   │   ├── 2024_03_13_000007_create_menu_items_table.php
│   │   └── 2024_03_13_000008_create_settings_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php              # Main seeder
│       ├── RoleSeeder.php                  # Spatie roles/permissions
│       └── SettingSeeder.php               # Default settings
├── config/
│   └── tuxcms.php                          # CMS configuration
├── routes/
│   └── api.php                             # API routes (v1)
└── API_DOCUMENTATION.md                    # Full API docs
```

## Models Overview

### Post
- Fields: title, slug, content, excerpt, featured_image, status, type, published_at, author_id
- SEO: HasSeo trait
- Media: HasMedia (featured_image, gallery collections)
- Tags: HasTags (Spatie)
- Relationships: author, categories, comments, seo
- Scopes: published(), ofType($type), scheduled(), byCategory(), byAuthor(), byTag()

### Category
- Fields: name, slug, description, parent_id, order
- SEO: HasSeo trait
- Relationships: posts, parent, children, seo
- Supports nested categories (hierarchy)

### Comment
- Fields: body, author_name, author_email, post_id, parent_id, status, user_id
- Relationships: post, parent, replies, user
- Nested comments (replies to comments)

### Seo (Polymorphic)
- Supports meta tags, OG tags, Twitter cards, schema markup
- Fields: meta_title, meta_description, keywords, og_*, twitter_*, canonical_url, robots, schema_markup, custom_head
- Attachable to Post, Category, and other models

### Menu & MenuItem
- Hierarchical structure
- Support custom URLs or links to Post/Category
- Nested menu items

### Setting
- Key-value store for site settings
- Grouped by: site, seo, social, email
- Static methods: get(), set(), getGrouped()

## API Features

### Authentication
- Register, Login, Logout with Sanctum
- Token-based authentication
- User profile endpoint

### Posts
- List with pagination (15 per page default)
- Filter by: status, type, category, tag, author, search
- Sort by: title, published_at, created_at
- Include relations: author, categories, tags, seo
- Full CRUD operations (protected)
- Auto-slug generation

### Categories
- Tree endpoint for hierarchical structure
- Full CRUD
- Filter and sort support

### Comments
- List per post (approved only)
- Nested replies
- Create (public), approve/delete (protected)
- Auto-moderation (pending by default)

### Menus
- Full CRUD with nested items
- Support custom links and Post/Category links
- Ordered items

### SEO
- Get/update SEO for any model
- Meta tags generation
- Open Graph support
- Twitter Cards
- JSON-LD schema markup
- Canonical URLs
- Robots directives

### Search
- Full-text search across title, content, excerpt
- Returns published posts only
- Paginated results

### Sitemap
- XML sitemap generation
- Includes posts and categories
- Lastmod timestamps
- Uses proper XML format

### Settings
- Get all or by group
- CRUD operations (protected)
- Key-value pairs

### Media
- Upload with Spatie MediaLibrary
- List, get, delete
- Collections: featured_image, gallery, attachments
- Custom properties: alt_text, caption

## Database Tables

### posts
- id, title, slug (unique), content, excerpt, featured_image
- status (enum: draft, published, scheduled, archived)
- type (enum: post, page, custom)
- published_at, author_id (FK users)
- created_at, updated_at

### categories
- id, name, slug (unique), description
- parent_id (self-referencing FK)
- order (for sorting)
- created_at, updated_at

### post_category
- Pivot table for posts and categories

### comments
- id, body, author_name, author_email
- post_id (FK posts), parent_id (self-referencing), user_id (nullable FK users)
- status (enum: pending, approved, spam)
- created_at, updated_at

### seos
- id, seoable_id, seoable_type (polymorphic)
- meta_title, meta_description, meta_keywords
- og_title, og_description, og_image, og_type
- twitter_card, twitter_title, twitter_description, twitter_image
- canonical_url, robots, schema_markup (JSON), custom_head
- created_at, updated_at

### menus
- id, name, slug (unique), location
- created_at, updated_at

### menu_items
- id, menu_id (FK menus), title, url, target
- parent_id (self-referencing FK), order
- type (enum: custom, post, category, page)
- linkable_id, linkable_type (polymorphic)
- created_at, updated_at

### settings
- id, key (unique), value (JSON), group
- created_at, updated_at

## Installed Packages

- laravel/sanctum - API authentication
- spatie/laravel-sluggable - Auto slug generation
- spatie/laravel-medialibrary - Media management
- spatie/laravel-tags - Tagging system
- spatie/laravel-permission - Roles & permissions
- spatie/laravel-query-builder - Advanced filtering/sorting
- spatie/laravel-translatable - Multi-language support (ready to use)

## Configuration

Edit `/config/tuxcms.php` to customize:
- API prefix and pagination
- Posts settings (per page, comments, auto-publish)
- Media settings (file size, extensions, collections)
- SEO settings (sitemap, schema markup, og fallback)
- Cache settings

## Seeders

### RoleSeeder
Creates four roles with permissions:
- **admin**: All permissions
- **editor**: Create/edit/publish posts, manage categories, comments, media, menus
- **author**: Create posts, manage own posts, create/delete own comments, upload media
- **subscriber**: View posts, create comments

### SettingSeeder
Initializes default settings:
- Site name, description, URL, posts per page
- Comment settings
- SEO settings
- Social media links
- Email settings

## Default Routes

All routes prefixed with `/api/v1`:

**Public:**
- GET /health
- POST /auth/register
- POST /auth/login
- GET /posts, /posts/{id}
- GET /categories, /categories/{id}, /categories/tree
- GET /posts/{id}/comments
- POST /comments
- GET /menus, /menus/{id}
- GET /settings, /settings/{key}, /settings/grouped
- GET /seo/meta-tags
- GET /search
- GET /sitemap.xml
- GET /media, /media/{id}

**Protected (Bearer token required):**
- POST /auth/logout, GET /auth/me
- POST/PUT/DELETE /posts, /categories, /comments, /menus
- POST/PUT/DELETE /settings
- GET/PUT /seo
- POST /media, DELETE /media/{id}

## File Paths

### Models
- `/app/Models/Post.php`
- `/app/Models/Category.php`
- `/app/Models/Comment.php`
- `/app/Models/Seo.php`
- `/app/Models/Menu.php`
- `/app/Models/MenuItem.php`
- `/app/Models/Setting.php`

### Controllers
- `/app/Http/Controllers/Api/V1/PostController.php`
- `/app/Http/Controllers/Api/V1/CategoryController.php`
- `/app/Http/Controllers/Api/V1/CommentController.php`
- `/app/Http/Controllers/Api/V1/MenuController.php`
- `/app/Http/Controllers/Api/V1/SeoController.php`
- `/app/Http/Controllers/Api/V1/SettingController.php`
- `/app/Http/Controllers/Api/V1/MediaController.php`
- `/app/Http/Controllers/Api/V1/AuthController.php`
- `/app/Http/Controllers/Api/V1/SearchController.php`
- `/app/Http/Controllers/Api/V1/SitemapController.php`

### Form Requests
- `/app/Http/Requests/StorePostRequest.php`
- `/app/Http/Requests/UpdatePostRequest.php`
- `/app/Http/Requests/StoreCategoryRequest.php`
- `/app/Http/Requests/UpdateCategoryRequest.php`
- `/app/Http/Requests/StoreCommentRequest.php`
- `/app/Http/Requests/StoreMenuRequest.php`
- `/app/Http/Requests/UpdateSettingRequest.php`
- `/app/Http/Requests/LoginRequest.php`
- `/app/Http/Requests/RegisterRequest.php`

### Resources
- `/app/Http/Resources/PostResource.php`
- `/app/Http/Resources/PostCollection.php`
- `/app/Http/Resources/CategoryResource.php`
- `/app/Http/Resources/CommentResource.php`
- `/app/Http/Resources/MenuResource.php`
- `/app/Http/Resources/MenuItemResource.php`
- `/app/Http/Resources/SeoResource.php`
- `/app/Http/Resources/SettingResource.php`
- `/app/Http/Resources/UserResource.php`
- `/app/Http/Resources/MediaResource.php`

### Migrations
- `/database/migrations/2024_03_13_000001_create_posts_table.php`
- `/database/migrations/2024_03_13_000002_create_categories_table.php`
- `/database/migrations/2024_03_13_000003_create_post_category_table.php`
- `/database/migrations/2024_03_13_000004_create_comments_table.php`
- `/database/migrations/2024_03_13_000005_create_seos_table.php`
- `/database/migrations/2024_03_13_000006_create_menus_table.php`
- `/database/migrations/2024_03_13_000007_create_menu_items_table.php`
- `/database/migrations/2024_03_13_000008_create_settings_table.php`

### Seeders
- `/database/seeders/DatabaseSeeder.php`
- `/database/seeders/RoleSeeder.php`
- `/database/seeders/SettingSeeder.php`

### Traits
- `/app/Traits/HasSeo.php`

### Middleware
- `/app/Http/Middleware/EnsureJsonResponse.php`

### Providers
- `/app/Providers/TuxCmsServiceProvider.php`

### Configuration
- `/config/tuxcms.php`

### Routes
- `/routes/api.php`

## SEO Features

The CMS includes comprehensive SEO support:

### Meta Tags
- Title, description, keywords
- Auto-generated from post title/excerpt
- Customizable via SEO resource

### Open Graph Tags
- og:title, og:description, og:image, og:type
- Optimized for social media sharing
- Automatic og:url set to canonical URL

### Twitter Cards
- twitter:card (summary_large_image by default)
- twitter:title, twitter:description, twitter:image
- Full Twitter sharing optimization

### Schema Markup (JSON-LD)
- Automatic BlogPosting schema for posts
- CollectionPage schema for categories
- Customizable schema_markup field
- Proper microdata for search engines

### Canonical URLs
- Auto-generated from post/category slug
- Customizable per resource
- Prevents duplicate content issues

### Robots Directives
- index/noindex control
- follow/nofollow control
- Default: index, follow

### Sitemap
- Auto-generated XML sitemap
- Includes all published posts and categories
- Proper lastmod timestamps
- Follows XML sitemap specification

## Getting Started

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

### 4. Start Development Server
```bash
php artisan serve
```

### 5. Access API
- Health check: `GET http://localhost:8000/api/v1/health`
- API docs: See `API_DOCUMENTATION.md`

## Testing

```bash
php artisan test
```

## Production Checklist

- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Configure database (production)
- [ ] Set up proper .env variables
- [ ] Run migrations on production
- [ ] Configure media storage (S3 recommended)
- [ ] Set up cache (Redis recommended)
- [ ] Configure CORS if needed
- [ ] Set up API rate limiting
- [ ] Configure monitoring/logging
- [ ] Test all API endpoints
- [ ] Set up backup strategy
