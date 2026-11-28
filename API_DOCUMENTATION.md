# TuxCMS API

Everything lives under `/api/v1`. Responses are always JSON, whatever the
request's `Accept` header says.

- [Conventions](#conventions)
- [Authentication](#authentication)
- [Pages](#pages)
- [Per-page SEO](#per-page-seo)
- [Search](#search)
- [Public forms](#public-forms)
- [Jobs](#jobs)
- [Applications and enquiries](#applications-and-enquiries)
- [Settings](#settings)
- [Media](#media)
- [Publishing](#publishing)

## Conventions

**Envelope.** Successful responses put the payload in `data`. Writes usually add
a `message`. Lists add `meta` with `current_page`, `last_page` and `total`
(most also include `per_page`).

**Auth.** Endpoints marked *token* need `Authorization: Bearer <token>`. Without
one they answer `401` with `{"message": "Unauthenticated."}`.

**Errors.** Validation failures answer `422`:

```json
{
  "message": "The title field is required.",
  "errors": { "title": ["The title field is required."] }
}
```

A missing record is `404`. Throttled endpoints answer `429` when the limit is
passed.

**Dates** are ISO 8601 strings.

## Authentication

There is no registration endpoint. Accounts are made on the server with
`php artisan user:create`.

### POST /auth/login

Throttled to 5 a minute.

```json
{ "email": "editor@example.com", "password": "secret-password" }
```

```json
{
  "data": { "id": 1, "name": "Editor", "email": "editor@example.com" },
  "token": "1|abc...",
  "message": "Login successful"
}
```

Wrong credentials answer `401` with `{"message": "Invalid credentials"}`.

### POST /auth/logout *(token)*

Revokes the token the request was made with.

### GET /auth/me *(token)*

The signed-in user.

## Pages

The read endpoints are open, so the public site and the builder need no token.
They behave differently with one: a visitor sees only published pages and
visible sections, an editor sees drafts and hidden sections too. A page counts
as published when its status is `published` and `published_at` is empty or in
the past.

### The page object

```json
{
  "id": 4,
  "title": "Web design",
  "slug": "web-design",
  "path": "/services/web-design",
  "full_path": "/services/web-design",
  "content": null,
  "excerpt": "Sites that load fast.",
  "template": "crystal",
  "status": "published",
  "is_scheduled": false,
  "order": 2,
  "is_homepage": false,
  "show_in_nav": true,
  "nav_label": null,
  "custom_fields": null,
  "published_at": "2026-11-11T09:00:00+00:00",
  "created_at": "2026-11-10T14:02:11+00:00",
  "updated_at": "2026-11-24T16:40:03+00:00"
}
```

`is_scheduled` is true for a published page dated in the future. `author`, `parent`, `children`,
`sections`, `seo`, `featured_image` and `gallery` appear when the endpoint
loads them.

A section:

```json
{
  "id": 40,
  "key": "hero",
  "type": "hero",
  "title": "Sites that load fast",
  "content": "Built to be read.",
  "data": { "image": "/storage/12/office.jpg", "image_alt": "The studio" },
  "order": 0,
  "is_visible": true
}
```

`type` is one of `hero`, `text`, `features`, `stats`, `testimonials`, `team`,
`faq`, `cta`, `contact`, `contact_form`, `map`, or a type declared in the
`theme.json` of the theme that renders the page (see
[GET /section-types](#get-section-typestemplate-token)). A theme type's `title`,
`content` and `data` are checked against its schema.

### GET /pages

Paginated, sorted by `order` unless told otherwise.

| Parameter | Meaning |
| --- | --- |
| `filter[status]`, `filter[template]`, `filter[parent_id]`, `filter[is_homepage]` | Exact match |
| `filter[title]` | Partial match |
| `search` | Partial match on title or slug |
| `sort` | `title`, `order`, `created_at`, `updated_at`, `published_at`; prefix `-` to reverse |
| `include` | Any of `sections`, `seo`, `author`, `children`, `parent`, `media`, comma separated |
| `per_page` | 1 to 100, default 15 |

### GET /pages/tree

Every page nested under its parent through `children`, ordered by `order` then
title.

### GET /pages/homepage

The published homepage with its visible sections, SEO and media.

### GET /pages/resolve?path=/services/web-design

Finds a page by its full path, walking the tree one segment at a time. An empty
path or `/` returns the homepage. Use this for nested pages: slugs are only
unique among siblings.

### GET /pages/{slugOrId}

One page with sections, SEO, author, children and media. A slug that exists
under more than one parent matches the first found, so prefer `resolve` or the
id.

### POST /pages *(token)*

Only `title` is required. `201` on success.

| Field | Rule |
| --- | --- |
| `title` | Required, up to 255 |
| `slug` | Optional; generated from the title when omitted |
| `content`, `excerpt` | Optional; excerpt up to 1000 |
| `template` | A built-in layout (`default`, `landing`, `contact`, `about`, `services`, `blank`) or an installed theme |
| `status` | `draft`, `published` or `archived` |
| `parent_id` | An existing page id |
| `order` | Integer, 0 or more |
| `is_homepage`, `show_in_nav` | Boolean. Setting a homepage unsets the previous one |
| `nav_label` | Up to 255 |
| `custom_fields` | Object |
| `published_at` | Date. A future date schedules the page |
| `sections` | Array of section objects; `type` is required on each |
| `seo` | Object, the same fields as [per-page SEO](#per-page-seo) |

A top-level slug of `admin`, `api`, `build`, `storage` or `up` is refused with
a `422`, because each is a real directory in the document root.

### PUT /pages/{page} *(token)*

`{page}` is the id or the slug. Send only what changes.

`sections`, when present, replaces the page's whole list: a section with an
`id` belonging to this page is updated in place, one without is created, and
any stored section missing from the list is deleted along with its media. Leave
`sections` out to keep them untouched.

A change is snapshotted, so the previous state can be restored.

A `template` change that would leave sections the new theme cannot draw is a
`422` on `template` naming them. Remove or replace them in the same request,
or first.

### GET /section-types?template= *(token)*

The section types a page with this template may hold: the built-in ones, then
those declared by the theme that renders it (the template's theme, else
`SITE_THEME`). Leave `template` out for a page with none. An unknown template
is a `422`.

```json
{
  "data": {
    "theme": "shopfront",
    "types": [
      {
        "type": "hero",
        "source": "base",
        "label": "Hero",
        "blurb": "Large banner with heading and call-to-action.",
        "fields": {
          "title": { "type": "text", "label": "Headline", "inspector": false },
          "data.alignment": { "type": "select", "label": "Alignment", "options": [{ "value": "left", "label": "Left" }, { "value": "center", "label": "Center" }] }
        },
        "defaults": { "title": "Your headline goes here", "content": "…", "data": { "alignment": "center" } }
      }
    ]
  }
}
```

`source` is `base` or `theme`. `fields` maps a path (`title`, `content`,
`data.<key>`) to its field; ARCHITECTURE.md lists the field types. `defaults`
is what a new section of the type starts with.

### GET /pages/{page}/revisions *(token)*

The 30 most recent earlier states, newest first.

```json
{
  "data": [
    {
      "id": 91,
      "created_at": "2026-11-24T16:40:03+00:00",
      "author": "Editor",
      "snapshot": { "title": "Web design", "sections": [] }
    }
  ]
}
```

To restore one, `PUT` its `snapshot` back to the page.

### DELETE /pages/{page} *(token)*

A soft delete.

## Per-page SEO

### GET /pages/{page}/seo *(token)*
### PUT /pages/{page}/seo *(token)*

All fields optional:

`meta_title`, `og_title`, `twitter_title` (up to 255); `meta_description`,
`meta_keywords`, `og_description`, `twitter_description`, `og_image`,
`twitter_image` (up to 500); `og_type`, `twitter_card` (up to 50);
`canonical_url` (a URL); `robots` (up to 100); `schema_markup` (object);
`custom_head` (up to 5000).

## Search

### GET /search?q=design

Published pages whose title, content or excerpt contains `q` (2 to 200
characters), most recently updated first. `limit` sets the page size, 1 to 100,
default 15. `meta` echoes the `query`.

## Public forms

Both forms are throttled to 5 a minute and share two anti-spam fields:

- `website` must be absent or empty. It is a hidden field people never see.
- `form_token` should carry a token from `GET /form-token`, fetched when the
  form page opens. Submissions without one, or sent within three seconds of
  it, are treated as automated.

A submission the filter judges to be spam still answers `201` with the usual
thank you. It is stored and shown in the dashboard but not emailed.

### GET /form-token

Throttled to 30 a minute, never cached.

```json
{ "token": "1795000000.4f2c..." }
```

### POST /enquiries

| Field | Rule |
| --- | --- |
| `type` | Required: `staff_request`, `registration` or `contact` |
| `name` | Required, up to 255 |
| `email` | Required |
| `organisation`, `phone` | Optional |
| `message` | Optional, up to 10000 |
| `consent` | Must be accepted |
| `details` | Optional object of up to 20 extra answers: short values or flat lists |

```json
{
  "message": "Thank you. We have your details and will be in touch shortly.",
  "reference": 57
}
```

The enquiry is emailed to the `enquiry_email_<type>` setting, falling back to
`enquiry_email`, then `contact_email`.

### POST /job-applications

Multipart, because of the CV.

| Field | Rule |
| --- | --- |
| `job` | Required: the slug of a published vacancy |
| `name`, `email` | Required |
| `phone` | Optional |
| `cover_letter` | Required, up to 10000 |
| `cv` | Required file: pdf, doc or docx, up to 5 MB |

Answers `201` with a `message` and a `reference`. The application is emailed to
the vacancy's `application_email`, falling back to the `contact_email` setting.

## Jobs

All *token*. Vacancies reach the public through the generated careers pages,
not through the API.

### GET /jobs

Newest update first. `search` matches title or location; `per_page` is 1 to 50,
default 10.

### GET /jobs/{id}
### POST /jobs
### PUT /jobs/{id}

| Field | Rule |
| --- | --- |
| `title`, `slug`, `summary` | Required. The slug is unique and uses letters, numbers, dashes and underscores |
| `status` | Required: `draft`, `published` or `closed` |
| `location`, `job_type`, `hours`, `salary`, `employment_type`, `description` | Optional text |
| `responsibilities`, `essential`, `desirable`, `benefits` | Optional lists of strings |
| `application_email` | Optional; where applications for this vacancy go |
| `published_at`, `closes_at` | Optional dates |

Publishing a vacancy with no `published_at` stamps it with the current time.
Create answers `201`.

### DELETE /jobs/{id}

Answers `204`.

## Applications and enquiries

All *token*.

### GET /job-applications
### GET /enquiries

Newest first; `per_page` is 1 to 100, default 20. `held=1` lists only what the
spam filter is holding, and `meta.held` always carries the held count.
Enquiries also filter by `type`.

Each row includes `delivery_status` (`sent`, `failed` or `spam`) and, where the
filter flagged it, a `spam_reason`.

### GET /job-applications/{id}/cv

Downloads the CV under its original file name.

### POST /enquiries/{id}/release
### POST /job-applications/{id}/release

Sends a held submission on to the inbox after all. `422` if it is not being
held.

```json
{ "delivery_status": "sent", "message": "Sent to the inbox." }
```

## Settings

All *token*. A setting is a `key`, a `value` and a `group`.

| Endpoint | Does |
| --- | --- |
| `GET /settings` | Every setting; `?group=` narrows it |
| `GET /settings/grouped` | `{ group: { key: value } }` |
| `GET /settings/{key}` | One setting |
| `PUT /settings` | Bulk save: `{ "settings": { "site_name": "Acme", "contact_email": "hi@acme.test" } }`. One transaction; answers the full key and value map |
| `POST /settings` | Create: `key` (unique), `value`, optional `group` |
| `PUT /settings/{key}` | Update `value` and optionally `group` |
| `DELETE /settings/{key}` | Answers `204` |

The dashboard saves through the bulk endpoint.

## Media

All *token*.

### GET /media

`collection` and `search` (on name) filter; `per_page` is 1 to 100, default 15.

```json
{
  "id": 12,
  "name": "office",
  "file_name": "office.jpg",
  "mime_type": "image/jpeg",
  "size": 482113,
  "custom_properties": { "alt_text": "The studio", "caption": null },
  "url": "https://example.com/storage/12/office.jpg",
  "collection_name": "library"
}
```

### GET /media/{id}

### POST /media

Multipart.

| Field | Rule |
| --- | --- |
| `file` | Required: jpg, jpeg, png, gif, pdf, doc or docx, up to 10 MB |
| `model_type` | Optional: `Page` or `PageSection` |
| `model_id` | Required with `model_type` |
| `collection` | Optional |
| `alt_text`, `caption` | Optional |

With a model, the file attaches to that page or section. Without one, it goes
to the site-wide library, which is what the Media screen uses. Answers `201`.

### DELETE /media/{id}

Answers `204`.

## Publishing

### GET /site/status *(token)*

```json
{
  "data": {
    "last_published_at": "2026-11-24T16:41:10+00:00",
    "pending_changes": 3,
    "has_output": true
  }
}
```

### POST /site/publish *(token)*

Rebuilds the static site and waits for it. Throttled to 10 a minute.

```json
{
  "data": {
    "pages": 9,
    "files": 14,
    "pruned": 1,
    "last_published_at": "2026-11-24T16:41:10+00:00"
  },
  "message": "Site published."
}
```

`409` if a build is already running, `500` with the reason if the build fails.
A failed build leaves the live site as it was.

## Health

`GET /health` answers `{"status": "ok", "version": "1.0.0"}`. Laravel's own
check is at `/up`, outside `/api/v1`.
