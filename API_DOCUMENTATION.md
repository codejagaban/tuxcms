# TuxCMS API Documentation

A headless Laravel CMS with full SEO support. All endpoints are available under `/api/v1/`.

## Table of Contents

- [Authentication](#authentication)
- [Public Endpoints](#public-endpoints)
- [Protected Endpoints](#protected-endpoints)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Pagination](#pagination)

## Authentication

TuxCMS uses Laravel Sanctum for API authentication. All protected endpoints require a Bearer token.

### Register

```
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

Response: 201 Created
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  },
  "token": "TOKEN_HERE",
  "message": "User registered successfully"
}
```

### Login

```
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

Response: 200 OK
{
  "data": {...},
  "token": "TOKEN_HERE",
  "message": "Login successful"
}
```

### Logout

```
POST /api/v1/auth/logout
Authorization: Bearer TOKEN_HERE

Response: 200 OK
{
  "message": "Logged out successfully"
}
```

### Get Current User

```
GET /api/v1/auth/me
Authorization: Bearer TOKEN_HERE

Response: 200 OK
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  }
}
```

## Public Endpoints

### Posts

#### List Posts

```
GET /api/v1/posts
Query Parameters:
  - page=1 (pagination)
  - per_page=15
  - published=true (only published posts)
  - status=published|draft|scheduled|archived
  - type=post|page|custom
  - category=slug
  - tag=name
  - author_id=1
  - search=keyword
  - include=author,categories,tags,seo

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "title": "Post Title",
      "slug": "post-title",
      "content": "...",
      "excerpt": "...",
      "featured_image": "url",
      "status": "published",
      "type": "post",
      "published_at": "2024-03-13T00:00:00Z",
      "author": {...},
      "categories": [...],
      "tags": ["tag1", "tag2"],
      "seo": {...},
      "meta_tags": {...},
      "og_tags": {...},
      "twitter_tags": {...},
      "canonical_url": "https://example.com/post-title",
      "created_at": "2024-03-13T00:00:00Z",
      "updated_at": "2024-03-13T00:00:00Z"
    }
  ],
  "meta": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4
  },
  "links": {
    "first": "http://example.com/api/v1/posts?page=1",
    "last": "http://example.com/api/v1/posts?page=4",
    "prev": null,
    "next": "http://example.com/api/v1/posts?page=2"
  }
}
```

#### Get Single Post

```
GET /api/v1/posts/{slug-or-id}
Query Parameters:
  - include=author,categories,tags,seo

Response: 200 OK
{
  "data": {...}
}
```

### Categories

#### List Categories

```
GET /api/v1/categories
Query Parameters:
  - parent_id=1
  - search=keyword
  - include=parent,children,seo
  - sort=name|order|created_at

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "Category Name",
      "slug": "category-name",
      "description": "...",
      "order": 0,
      "parent_id": null,
      "parent": null,
      "children": [...],
      "seo": {...},
      "meta_tags": {...},
      "canonical_url": "https://example.com/category-name",
      "created_at": "2024-03-13T00:00:00Z",
      "updated_at": "2024-03-13T00:00:00Z"
    }
  ]
}
```

#### Get Category Tree

```
GET /api/v1/categories/tree

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "Parent Category",
      "children": [
        {
          "id": 2,
          "name": "Child Category",
          "children": [...]
        }
      ]
    }
  ]
}
```

#### Get Single Category

```
GET /api/v1/categories/{slug-or-id}
```

### Comments

#### List Comments for a Post

```
GET /api/v1/posts/{post_id}/comments
Query Parameters:
  - limit=20 (alternative to pagination)

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "body": "Great post!",
      "author_name": "John",
      "author_email": "john@example.com",
      "status": "approved",
      "post_id": 1,
      "parent_id": null,
      "user": null,
      "replies": [...],
      "created_at": "2024-03-13T00:00:00Z",
      "updated_at": "2024-03-13T00:00:00Z"
    }
  ]
}
```

#### Create Comment

```
POST /api/v1/comments
Content-Type: application/json

{
  "body": "Great post!",
  "author_name": "John Doe",
  "author_email": "john@example.com",
  "post_id": 1,
  "parent_id": null
}

Response: 201 Created
{
  "data": {...},
  "message": "Comment submitted. It will be visible once approved."
}
```

### Menus

#### List Menus

```
GET /api/v1/menus
Include: items

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "Main Menu",
      "slug": "main-menu",
      "location": "header",
      "items": [
        {
          "id": 1,
          "title": "Home",
          "url": "https://example.com",
          "target": "_self",
          "order": 0,
          "type": "custom",
          "children": [...]
        }
      ]
    }
  ]
}
```

#### Get Single Menu

```
GET /api/v1/menus/{slug-or-id}
```

### Settings

#### List Settings

```
GET /api/v1/settings
Query Parameters:
  - group=site|seo|social|email

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "key": "site_name",
      "value": "TuxCMS",
      "group": "site"
    }
  ]
}
```

#### Get Grouped Settings

```
GET /api/v1/settings/grouped

Response: 200 OK
{
  "data": {
    "site": {
      "site_name": "TuxCMS",
      "site_description": "...",
      ...
    },
    "seo": {...},
    "social": {...}
  }
}
```

#### Get Single Setting

```
GET /api/v1/settings/{key}
```

### SEO

#### Get Meta Tags

```
GET /api/v1/seo/meta-tags
Query Parameters:
  - slug=post-slug (required)
  - type=post|category (required)

Response: 200 OK
{
  "meta_tags": {
    "title": "Post Title",
    "description": "Meta description...",
    "keywords": "keyword1, keyword2",
    "canonical": "https://example.com/post-title",
    "robots": "index, follow"
  },
  "og_tags": {
    "og:title": "Post Title",
    "og:description": "...",
    "og:image": "https://example.com/image.jpg",
    "og:type": "article",
    "og:url": "https://example.com/post-title"
  },
  "twitter_tags": {
    "twitter:card": "summary_large_image",
    "twitter:title": "Post Title",
    "twitter:description": "...",
    "twitter:image": "https://example.com/image.jpg"
  },
  "schema_markup": {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "Post Title",
    ...
  }
}
```

### Search

#### Search Posts

```
GET /api/v1/search
Query Parameters:
  - q=search-term (required)
  - limit=20 (optional)

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "title": "Matching Post",
      ...
    }
  ],
  "meta": {...},
  "links": {...}
}
```

### Sitemap

#### Get XML Sitemap

```
GET /api/v1/sitemap.xml

Response: 200 OK (Content-Type: application/xml)
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://example.com/api/v1/posts</loc>
    <lastmod>2024-03-13T00:00:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
  </url>
  ...
</urlset>
```

### Media

#### List Media

```
GET /api/v1/media
Query Parameters:
  - page=1
  - per_page=15
  - collection=featured_image|gallery
  - search=filename

Response: 200 OK
{
  "data": [
    {
      "id": 1,
      "name": "image",
      "file_name": "image.jpg",
      "mime_type": "image/jpeg",
      "size": 25600,
      "custom_properties": {
        "alt_text": "Image alt text",
        "caption": "Image caption"
      },
      "url": "https://example.com/storage/posts/1/image.jpg",
      "collection_name": "featured_image",
      "created_at": "2024-03-13T00:00:00Z"
    }
  ],
  "meta": {...}
}
```

#### Get Single Media

```
GET /api/v1/media/{id}
```

## Protected Endpoints

All protected endpoints require Bearer token authentication:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Posts (CRUD)

```
POST /api/v1/posts
PUT /api/v1/posts/{id}
DELETE /api/v1/posts/{id}
```

### Categories (CRUD)

```
POST /api/v1/categories
PUT /api/v1/categories/{id}
DELETE /api/v1/categories/{id}
```

### Comments (Manage)

```
GET /api/v1/comments/{id}
PUT /api/v1/comments/{id}
DELETE /api/v1/comments/{id}
```

### Menus (CRUD)

```
POST /api/v1/menus
PUT /api/v1/menus/{id}
DELETE /api/v1/menus/{id}
```

### Settings (Write)

```
POST /api/v1/settings
PUT /api/v1/settings/{key}
DELETE /api/v1/settings/{key}
```

### SEO (Write)

```
GET /api/v1/seo
PUT /api/v1/seo
```

### Media (Upload/Delete)

```
POST /api/v1/media (upload file)
DELETE /api/v1/media/{id}
```

## Response Format

All responses are in JSON format with consistent structure:

### Success Response

```json
{
  "data": {...},
  "message": "Success message (optional)"
}
```

### Error Response

```json
{
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

## Error Handling

Common HTTP Status Codes:

- `200 OK` - Success
- `201 Created` - Resource created
- `204 No Content` - Deleted successfully
- `400 Bad Request` - Invalid input
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Permission denied
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

## Pagination

List endpoints support pagination:

Query Parameters:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

Response includes:
- `meta.total` - Total items
- `meta.per_page` - Items per page
- `meta.current_page` - Current page
- `meta.last_page` - Last page number
- `links.first` - Link to first page
- `links.last` - Link to last page
- `links.prev` - Link to previous page
- `links.next` - Link to next page

## Filtering & Sorting

Posts endpoint supports advanced filtering:

```
GET /api/v1/posts?filter[status]=published&filter[author_id]=1&sort=-created_at
```

Available filters:
- `status` - post status
- `type` - post type
- `author_id` - author ID
- `category` - category slug
- `tag` - tag name
- `search` - search term

Available sorts:
- `title`, `published_at`, `created_at`, `-published_at`, `-created_at`

## Includes

Use `include` parameter to load relationships:

```
GET /api/v1/posts?include=author,categories,tags,seo
```
