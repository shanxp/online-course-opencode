# Course CMS — Project Overview

## Architecture

A private, admin-managed Course Content Management System built on Laravel 13 with Tailwind CSS v4 and Alpine.js v3.

### Backend (Laravel 13)
- **PHP 8.4+** required
- MySQL/MariaDB for production; SQLite for development
- Blade templating (no Livewire)
- 3 Service classes: `FileStorageService`, `ActivityLoggerService`, `MediaSyncService`
- 5 Policies: Course, Folder, MediaFile, YouTubeVideo, User
- 7 Form Requests for validated CRUD operations
- 2 custom middleware: `AdminMiddleware` (role gate), `SetLocale` (session-based i18n)

### Frontend (Tailwind CSS v4 + Alpine.js v3)
- Vite build pipeline with Laravel plugin
- `<template x-for>` is **forbidden** — causes `cloneNode` error with Vite build; use individual elements with `x-show`
- `@alpinejs/collapse` plugin registered for smooth accordion animations
- Primary color: `#982315` (dark red) — overrides `--color-indigo-*` CSS variables so all `indigo-*` classes render in this color

---

## Key Design Decisions

### 1. Authentication
- **Username-based** login (not email) — `LoginController` uses `Auth::attempt(['username' => ..., 'password' => ...])`
- **Legacy password migration**: On failed bcrypt login, checks `old_password` via `SHA1(strtolower(username) + password)`. On match, migrates to bcrypt and clears `old_password`.
- **No self-registration** — admins create all accounts via `/admin/users`
- **Admin password reset**: generates 12-char random password, displayed inline on edit page with Copy button (no mailer needed)
- **User self password change**: `/profile` → requires current password + new password with confirmation; clears `old_password`

### 2. Authorization (Group-Based Permissions)
- Two roles via `roles` table (admin/user) — designed for future RBAC extension
- Users belong to **groups** (`group_user` pivot)
- Groups have course-level permissions (`group_course`: view/download) and folder-level permissions (`group_folder`: view/download)
- `User::accessibleCourses()` union of all group-level and folder-level access
- `canDownloadCourse()` / `canDownloadFolder()` checks group download permissions
- Old `course_user` and `folder_user` tables exist but are **unused**

### 3. Content Hierarchy
```
Course
├── Course Materials (folder_id = null)
│   ├── MediaFile (MP3/PDF)
│   └── YouTubeVideo
├── Folder (top-level, parent_id = null)
│   ├── MediaFile
│   ├── YouTubeVideo
│   └── Folder (child)
└── ...
```

- Unlimited folder nesting via self-referencing `parent_id`
- **Course Materials** (formerly "Uncategorized Content") displayed first, above folder accordion
- Folder accordion with search + pagination (20/page) for courses with many folders
- Sticky folders (`is_sticky`) sort first, then by `sort_order`

### 4. Media Protection
- All MP3 and PDF files are served through protected routes (no direct file URLs)
- `FileStorageService` handles store, delete, stream, download
- `MediaSyncService` + `php artisan media:sync` imports files from `MEDIA_SYNC_PATH`
- MP3 played via native `<audio>` player with download link
- PDF opens in new tab via protected stream route

### 5. Localization
- **German (`de`) is the default locale** via `APP_LOCALE=de` in `.env`
- English (`en`) also fully supported
- ~278 translation keys per language in `messages.php`
- Session-based locale persistence via `SetLocale` middleware
- All Blade views fully translated — no hardcoded English text
- Locale switcher in top-nav, login page, and welcome page nav

### 6. UI Components
- **Confirm dialog**: Alpine-powered modal replacing all native `confirm()` calls; triggered via `$dispatch('confirm-open', ...)`
- **Searchable single-select**: Alpine-powered dropdown with keyboard navigation, selected badge with clear
- **Searchable multi-select**: comma-separated IDs via hidden input; `disabledIds` prop for greyed-out already-granted items
- **Toast**: single-element `x-show` flash notification (no `x-for`)
- **Audio player**: HTML5 `<audio>` with download link
- **Breadcrumbs**: auto-generated from route segments

### 7. Admin Features
- Dashboard with stats (courses, users) + recent courses table
- Full CRUD for: Courses, Folders, Media, YouTube Videos, Users, Slideshow Images
- Group permission management with hierarchy-aware multi-select folder picker
- Activity log viewer with user/action filters
- User index with search (name/username/email) + status filter
- Slideshow image CRUD for homepage hero

### 8. Public Homepage
- Database-driven hero slideshow (`slideshow_images` table), managed via admin
- Course cards (filtered by `is_published` and `show_on_homepage`)
- Clickable `<a>` tag cards, locale switcher
- Guest users see "Sign in to view"; authenticated users see "View Content"; admins see "Edit"

---

## Database (18 Migrations)

| Migration | Purpose |
|-----------|---------|
| `create_users_table` | roles, users, sessions |
| `create_cache_table` | Cache |
| `create_jobs_table` | Jobs |
| `create_courses_table` | Courses |
| `create_folders_table` | Folders with parent_id |
| `create_media_files_table` | Media with course_id, folder_id |
| `create_youtube_videos_table` | YouTube videos |
| `create_course_user_table` | Legacy (unused) |
| `create_folder_user_table` | Legacy (unused) |
| `create_activity_logs_table` | Audit trail |
| `add_username_to_users_table` | Username column |
| `create_groups_table` | Permission groups |
| `create_group_user_table` | Group memberships |
| `create_group_course_table` | Course-level permissions |
| `create_group_folder_table` | Folder-level permissions |
| `add_show_on_homepage_to_courses_table` | Homepage visibility toggle |
| `add_old_password_to_users_table` | Legacy password migration |
| `create_slideshow_images_table` | Homepage hero images |
| `add_is_sticky_to_folders_table` | Folder pinning |

---

## Seed Data

| Seeder | Details |
|--------|---------|
| `DatabaseSeeder` | Admin + user accounts, "Students" group, 3 courses (27+ folders), 5 YouTube videos, group permissions, 3 slideshow images |
| `UserSeeder` | 150 realistic users (3 admins, 10 inactive); idempotent (skips if >10 users exist) |

**Default credentials**: `admin` / `password` and `user` / `password`

---

## Development Timeline

1. Project scaffold (Laravel + Vite + Tailwind v4 + Alpine)
2. Auth system (username login, legacy SHA1 migration, roles)
3. Admin layout (sidebar with flash prevention, top-nav, responsive)
4. Course CRUD with show page (folders, media, YouTube)
5. Folder CRUD (unlimited nesting, reorder up/down, position-based)
6. Media upload + protected streaming (MP3 audio player, PDF viewer)
7. YouTube video CRUD (external links)
8. User management (CRUD, password reset, admin edit password)
9. Group-based permission system (UI with hierarchy-aware multi-select)
10. User dashboard + course viewer (permission-filtered content)
11. Activity logging (all CRUD + login/logout/download tracked)
12. Media sync artisan command (from configurable path)
13. Homepage (DB-driven slideshow, course cards, guest/authenticated)
14. UI polish (mobile, Alpine hydration, overflow fixes)
15. **Full localization** (all views translated, German default, locale switcher)
16. **Confirm dialog** (Alpine modal replaces native confirm())
17. **Slideshow admin CRUD** (manage hero images via admin panel)
18. **UserSeeder** (150 realistic users)
19. **Folder sticky** (pin/unpin, sticky-first sort)
20. **Pagination customization** (first/last buttons, translatable labels)
21. **User index filters** (search + status)
22. **Input standardization** (uniform form field styling)
23. **Folder accordion** (collapse plugin, search, pagination)
24. **Primary color change** (dark red #982315)

---

## Configuration

| Env Var | Default | Purpose |
|---------|---------|---------|
| `APP_LOCALE` | `de` | Default language (en/de) |
| `MEDIA_SYNC_PATH` | `storage/app/media-sync` | Directory for media sync |
| `THUMBNAIL_MAX_SIZE` | `2048` | Max thumbnail size in KB |

---

## Key Constraints

- **No `<template x-for>` or `<template x-if>`** — Alpine cloneNode bug with Vite
- **Sidebar flash prevention**: `data-sidebar-desktop` attribute + inline critical CSS
- **No `x-cloak`** on admin/user layouts (causes white screen during load)
- **YouTubeVideo model**: `$table = 'youtube_videos'` (prevents incorrect pluralization to `you_tube_videos`)
- **All input fields**: `text-base px-4 py-3` for consistency
- **Checkbox unchecked handling**: hidden `0` field before checkbox
