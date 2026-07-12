# Course CMS — Project Requirements

## Overview

A private, admin-managed Course Content Management System built with Laravel 13, Tailwind CSS v4, and Alpine.js. Users access curated courses containing folders, MP3 audio, PDF documents, and YouTube videos. All media is protected (no direct URLs). Admins manage everything; users cannot self-register.

### Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.4+) |
| Frontend | Tailwind CSS v4 + Alpine.js v3 |
| Build | Vite (Laravel Vite Plugin) |
| Database | SQLite (dev) / MySQL (prod) |
| Fonts | Instrument Sans (via Bunny CDN) |

---

## 1. Authentication & Authorization

### 1.1 Login
- **Username-based** — no email login
- Two roles via `roles` table (admin/user) for future RBAC extension
- No self-registration; admins create all accounts
- **Legacy password support**: users table has `old_password` (varchar 40, nullable) for importing users from legacy systems. On login, if bcrypt `Auth::attempt()` fails and `old_password` is populated, the controller computes `strtoupper(sha1(strtolower(username) + password))` and matches against the stored value. On match, the password is migrated to bcrypt and `old_password` is cleared.

### 1.2 User Model (`App\Models\User`)
- Fields: name, username (unique), email, password, old_password (varchar 40, nullable), role_id (FK to roles), is_active (boolean), last_login_at (timestamp, nullable)
- `$fillable` includes `last_login_at`; casts includes `'last_login_at' => 'datetime'`
- `isAdmin()` — checks `role->slug === 'admin'`
- `hasRole(slug)` — generic role check
- `accessibleCourses()` — returns courses the user can view (via group permissions):
  - Course-level: any group the user belongs to has course permission
  - Folder-level fallback: any group has permission on any folder (including sub-folders) within the course
- `canViewCourse(courseId)` — checks accessibleCourses exists
- `canDownloadCourse(courseId)` — checks any user group has download permission on the course
- `canDownloadFolder(folderId)` — checks any user group has download permission on the folder
- `hasCourseLevelAccess(courseId)` — checks group_course pivot exists (separate from folder-only access)
- `accessibleFolderIdsInCourse(courseId)` — returns IDs of folders the user has access to (including ancestor chain)

### 1.3 Middleware
- `AdminMiddleware` — `abort(403)` if user is not admin; registered as `'admin'` in routes
- `SetLocale` — reads `session('locale')` and sets `App::setLocale()`; appended to the `web` middleware group in `bootstrap/app.php`

### 1.4 Policies
- **CoursePolicy**: view (admin OR groups), create/update/delete (admin only)
- **FolderPolicy**: view (admin OR canViewCourse), create/update/delete (admin only)
- **MediaFilePolicy**: view/stream (admin OR canViewCourse), download (admin OR canDownloadCourse OR canDownloadFolder), create/update/delete (admin only)
- **YouTubeVideoPolicy**: view (admin OR canViewCourse), create/update/delete (admin only)
- **UserPolicy**: all (admin only)

---

## 2. Permission System (Group-Based)

### 2.1 Groups
- `groups` table: id, name, description
- `group_user` pivot: user_id, group_id
- `group_course` pivot: group_id, course_id, permission (view|download)
- `group_folder` pivot: group_id, folder_id, permission (view|download)

### 2.2 Permission Logic
1. A user belongs to one or more **groups**
2. A group has **course-level permissions** (via group_course) and/or **folder-level permissions** (via group_folder)
3. Course-level `view` → user can stream all media in that course
4. Course-level `download` → user can also download all media in that course
5. Folder-level `download` → user can download files in that specific folder (when course access is folder-only)
6. The union of all groups the user belongs to determines total access

### 2.3 Permission UI (`/admin/permissions`)
- Left panel: create group form + group list (with member counts)
- Right panel (group selected):
  - Group name/description edit
  - Member management (add/remove users via multi-select)
  - Course permissions (add/remove courses with view/download)
  - Folder permissions (add/remove folders with view/download; hierarchy-aware multi-select)
- All cards use `min-w-0 truncate` to prevent flex overflow from long text
- **Multi-select**: both user and folder selectors use `<x-searchable-multi-select>` — submit comma-separated IDs, show selected items as removable tags
- **Disabled items**: already-granted folders/users are greyed out (`opacity-50 cursor-not-allowed`) with an "already added" badge and cannot be selected

---

## 3. Course Content Hierarchy

```
Course
├── Folder (top-level, parent_id = null)
│   ├── MediaFile (MP3 or PDF)
│   ├── YouTubeVideo
│   ├── Folder (child, parent_id set)
│   │   ├── MediaFile
│   │   └── YouTubeVideo
│   └── ...
└── Uncategorized (folder_id = null)
    ├── MediaFile
    └── YouTubeVideo
```

### 3.1 Courses
- Fields: title, slug (auto-generated), description, thumbnail, is_published (boolean), show_on_homepage (boolean, default true), sort_order, created_by (FK to users)
- Routes: `/admin/courses/*` (CRUD with show)
- Admin course show page: recursive folder tree with media displayed inline
- `folders()` relationship: only top-level (parent_id IS NULL), ordered by sort_order
- `allFolders()` relationship: all folders regardless of depth, ordered by sort_order

### 3.2 Folders
- Self-referencing via `parent_id` for unlimited nesting
- `sort_order` auto-assigned on create (`max() + 1` among siblings)
- `siblingBefore()` / `siblingAfter()` for reordering
- Routes: move-up/move-down via POST endpoints
- **Reorder logic**: position-based (array splice + sequential sort_order reassignment) — works even when all values are 0

### 3.3 Media Files
- MP3: streamed via HTML5 `<audio>` player (protected route, no direct URL)
- PDF: streamed via protected route, opened in new tab with download link
- File stored on `public` disk, path in `media_files` table
- `FileStorageService`: store, delete, stream, download
- `MediaSyncService`: sync MP3/PDF from `MEDIA_SYNC_PATH` into database

### 3.4 YouTube Videos
- `$table = 'youtube_videos'` (explicit table name to prevent `you_tube_videos` pluralization)
- Fields: youtube_id, url, title, description, course_id, folder_id, sort_order
- Displayed as external links (opens YouTube in new tab)

---

## 4. Routes & Navigation

### 4.1 Public / Guest Routes
| URI | Name | Action |
|-----|------|--------|
| `/` | `home` | Homepage with hero slideshow, published course cards, login prompt |
| `/login` | `login` | Login form (username) |
| `/locale/{locale}` | `locale.switch` | Switch site language (en/de) — stored in session |
| `/course/{slug}` | `courses.public.show` | Public course details page (thumbnail, description, sign-in prompt) |

### 4.2 Authenticated User Routes
| URI | Name | Action |
|-----|------|--------|
| `/dashboard` | `dashboard` | User dashboard with accessible courses — supports `?sort=` param |
| `/profile` | `profile` | Profile page with password change form |
| `/password` | `password.update` | POST — update password (current + new + confirm) |
| `/user/courses` | — | Redirects to dashboard |
| `/user/courses/{course:slug}` | `courses.show` | Course content viewer (folder/media/video display) — resolved by slug |
| `/media/{media}/stream` | `media.stream` | Protected media streaming |
| `/media/{media}/download` | `media.download` | Protected media download |

### 4.3 Admin Routes (prefix: `/admin`)
| URI | Name | Action |
|-----|------|--------|
| `GET /admin` | — | Redirects to `/admin/dashboard` |
| `GET /admin/dashboard` | `admin.dashboard` | Stats dashboard |
| `/admin/courses/*` | `admin.courses.*` | Full CRUD + show; slug field in create/edit with JS auto-generation |
| `/admin/folders/*` | `admin.folders.*` | CRUD + move-up/move-down + toggle-sticky |
| `/admin/media/*` | `admin.media.*` | Index, create, destroy, sync |
| `/admin/youtube-videos/*` | `admin.youtube-videos.*` | CRUD |
| `/admin/users/*` | `admin.users.*` | CRUD (except show) + reset-password; `GET /admin/users/{user}` redirects to edit |
| `/admin/permissions` | `admin.permissions.*` | Group management CRUD + add/remove members/courses/folders |
| `/admin/activity-logs` | `admin.activity-logs.index` | Activity log viewer (filterable by user) |

---

## 5. Admin Features

### 5.1 Dashboard
- Stats cards: total courses, published courses, total users, active users
- Recent courses table with ID column
- Courses sortable by Default, Title, Newest, Oldest via dropdown

### 5.2 Course Management
- Create/Edit: title, description (Quill.js WYSIWYG editor), slug (auto-generated from title, admin can override, unique), thumbnail upload, remove thumbnail checkbox, published toggle, show_on_homepage toggle
- Show page: folders listed as accordion with media/video content inline, folder reorder buttons; thumbnail left + description right in flex row description right)
- Thumbnail max size: configurable via `THUMBNAIL_MAX_SIZE` env var (default 2048KB)
- Created at column in course index table (with minute precision)
- Slug auto-generated on create and update via `booted` callbacks; validated as nullable + unique in form requests
- Courses sortable on homepage and user dashboard (Default, Title A-Z, Title Z-A, Newest, Oldest); Newest is default

### 5.3 Folder Management
- Create with parent selection (course + optional parent folder)
- Reorder via up/down chevron buttons; toggle sticky via bookmark button
- Unlimited nesting depth
- Filter by course
- Created at and updated at timestamps shown on folder accordion headers and sub-folders (with minute precision)

### 5.4 Media Management
- Upload: file (mp3/pdf), name, course, folder selector (hierarchy-aware dropdown with `buildFolderTree()`)
- Sync: `php artisan media:sync` — imports files from `MEDIA_SYNC_PATH`; optional course/folder arguments
- Folder selector validates folder belongs to selected course

### 5.5 User Management
- CRUD for users (username, name, email, password, role, active status)
- **Password reset**: `GET /admin/users/{user}/reset-password` — generates random 12-char password, redirects back to edit page with a yellow alert box showing the password and a Copy button; also clears `old_password`
- **Admin sets password on edit**: if password field is filled, it's hashed and `old_password` is cleared
- Groups column shows user's group memberships as badges
- Created at and last login columns in user index; both sortable (clickable column headers with ↑/↓ indicator)
- User edit page shows created, updated, and last login timestamps
- User list rows are clickable (navigate to edit page); actions column uses `@click.stop`
- `GET /admin/users/{user}` redirects to edit page (resource `show` was excluded)

### 5.6 Activity Logs
- Tracks: login, logout, CRUD operations, downloads, permission changes
- Filterable by user via multi-select dropdown (`<x-searchable-multi-select>` with `autosubmit` prop)

---

## 6. User Features

### 6.1 User Dashboard
- Lists courses the user can access via their groups
- Courses sortable by Default, Title (A-Z), Title (Z-A), Newest, Oldest via dropdown; Newest is default
- Links to course content viewer via `/user/courses/{slug}`

### 6.2 Profile Page (`/profile`)
- **Password change**: requires current password, new password with confirmation; validated via `current_password` rule
- On success: hashes new password into `password` column, clears `old_password`
- Route: `GET /profile` (named `profile`) — accessible via "Profile" nav item in sidebar

### 6.3 Course Content Viewer
- **Course-level access**: all folders and uncategorized content visible
- **Folder-level only**: only assigned folders (plus ancestors) visible; uncategorized content hidden
- Admin users see all content
- MP3 displayed with HTML5 `<audio>` player
- PDF displayed with "Download PDF" button (download route, no longer opens in new tab)
- YouTube videos shown as external links
- Folder accordion headers and sub-folders show created at timestamp (with minute precision)
- Thumbnail left + description right in flex row

---

## 7. UI / Layout

### 7.1 Layout Structure
- `layouts/app.blade.php` — base HTML shell with `<head>`, Vite assets, critical CSS, toast component
- `layouts/admin.blade.php` — sidebar + top-nav + content area for admin pages
- `layouts/user.blade.php` — same structure for user pages

### 7.2 Layout Structure Details
- **Desktop sidebar**: in-flow flex (not fixed) to prevent Alpine hydration flash
- **Mobile sidebar**: fixed overlay with slide transition (`x-transition`), triggered by hamburger
- `data-sidebar-desktop` attribute + inline `<style>` block ensures correct layout before Tailwind CSS loads
- Sidebar width `w-80` (was `w-64`)
- Logo container has `bg-white` background; logo at `h-14 w-auto`
- Collapsible width toggle (`w-64` / `w-16`) — removed, now fixed `w-80`
- Mobile sidebar uses `x-cloak` to prevent flash before Alpine initializes (no longer relies on CSS `display: none` override — that rule was removed as it was overriding Alpine's `x-show`)
- **Admin sidebar links** (order): Dashboard, Courses, Users, Media, YouTube, Permissions, Groups, Slideshow, Activity Logs
- **User sidebar links**: My Courses, Profile

### 7.3 Focus Ring Removal
- Global CSS in `resources/css/app.css` removes `outline`, `--tw-ring-*`, and `box-shadow` on focus for all `input`, `textarea`, and `select` elements
- Input border visibility: login form inputs use `border-2 border-gray-700` for clear visibility (Tailwind v4 omits `border-2` unless detected in source; initially didn't appear until after build)

### 7.4 Login Page
- Logo displayed at `h-16` wrapped in `<a href="{{ route('home') }}">` with `hover:opacity-80`
- Locale switcher in top-right corner
- "Back to Homepage" link below login button
- Form uses `autocomplete="off"`; password field uses `autocomplete="new-password"` to prevent browser autofill
- Input borders use `border-2 border-gray-700` for visibility

### 7.5 Locale Switcher
- Globe icon in `top-nav` showing current locale (`EN`/`DE`)
- Dropdown switches locale via `GET /locale/{locale}` route (stored in session)
- Also available on login page
- `SetLocale` middleware reads session and sets app locale on every request
- Default locale set via `APP_LOCALE=de` in `.env` (German default)

### 7.6 Components
- `<x-sidebar />` — desktop + mobile sidebar with nav items
- `<x-top-nav />` — header bar with sidebar toggle + user dropdown + logout
- `<x-breadcrumbs />` — breadcrumb navigation
- `<x-toast />` — session flash notification (success/error), single-message Alpine component (no `x-for`)
- `<x-audio-player />` — HTML5 audio player for MP3 files
- `<x-empty-state />` — placeholder when no data exists
- `<x-searchable-select />` — single-select searchable dropdown (Alpine-powered, keyboard navigation); supports `autosubmit` prop (auto-submits parent form on select/clear)
- `<x-searchable-multi-select />` — multi-select variant with removable tags, `disabledIds` prop for greyed-out items, submits comma-separated IDs via hidden input; supports `autosubmit` prop (used in activity log filter)
- `<x-sidebar-nav-items />` — shared sidebar navigation links for admin and user roles
- `<x-confirm-dialog />` — Alpine-powered modal confirm dialog; replaces all 17 native `confirm()` calls; triggered via `$dispatch('confirm-open', { action, method, message, isLink })`; includes backdrop, warning icon, Cancel/Confirm buttons; handles both form POST and link GET via `isLink` flag
- **Quill.js WYSIWYG editor** (not a Blade component) — loaded via CDN in `layouts/admin.blade.php`; initialized on `#editor` div; custom `< >` button toggles source code textarea; hidden `description` textarea populated on form submit; renders HTML descriptions safely via `{!! !!}`

### 7.7 Homepage (`/`)
- Sticky nav: app name logo (constrained to `max-width: 300px` via inline style, `py-4` padding), Courses anchor link, Login/Dashboard button, locale switcher, mobile hamburger menu
- **Hero slideshow**: full-width crossfading images, dark gradient overlay for readability, auto-advances every 5s, clickable dot navigation. Images are **database-driven** (`slideshow_images` table) — managed via admin CRUD (`/admin/slideshow-images`). No `<template x-for>` (uses individual elements to avoid Alpine `cloneNode` error)
- **Course cards**: responsive grid (1/2/3 columns), thumbnail (or gradient fallback), title, description (2-line clamp), fully clickable `<a>` tags linking to `/course/{slug}` (public details page). Sort dropdown: Default, Title A-Z, Title Z-A, Newest, Oldest (Newest is default).
- **Public course details page** (`/course/{slug}`): shows thumbnail left, description right in flex row with sign-in prompt (no folder content or materials shown to guests)
- **Footer**: dark background, copyright, login/dashboard link

### 7.8 Mobile Responsiveness
- All admin pages stack cards vertically below `lg:` breakpoint
- Sidebar toggle switches between desktop collapse and mobile overlay based on viewport width
- Forms use flex-wrap + min-widths to prevent overflow
- Text spans in permission list items use `min-w-0 truncate` to prevent flex overflow from long names

---

## 8. Database Schema Summary

### Core Tables
| Table | Key Columns |
|-------|------------|
| `roles` | id, name, slug |
| `users` | id, name, username (unique), email, password, old_password (varchar 40, nullable), role_id (FK), is_active |
| `courses` | id, title, slug, description, thumbnail, is_published, show_on_homepage (bool, default true), sort_order, created_by (FK) |
| `folders` | id, name, description, course_id (FK), parent_id (self-FK, nullable), sort_order |
| `media_files` | id, name, path, type, size, mime, disk, course_id (FK), folder_id (FK, nullable) |
| `youtube_videos` | id, youtube_id, url, title, description, course_id (FK), folder_id (FK, nullable), sort_order |
| `slideshow_images` | id, image_path, sort_order, is_active |

### Permission Tables (active)
| Table | Key Columns |
|-------|------------|
| `groups` | id, name, description |
| `group_user` | group_id, user_id |
| `group_course` | group_id, course_id, permission (view\|download) |
| `group_folder` | group_id, folder_id, permission (view\|download) |

### Legacy Tables (unused, still exist)
| Table | Notes |
|-------|-------|
| `course_user` | Old per-user course permissions — no longer referenced by any code |
| `folder_user` | Old per-user folder permissions — no longer referenced by any code |

### Other
| Table | Purpose |
|-------|---------|
| `activity_logs` | Audit trail (user_id, action, description, subject_type, subject_id, ip, user_agent) |

---

## 9. Development & Deployment

### 9.1 Setup Commands
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

### 9.2 Default Credentials
| User | Username | Password |
|------|----------|----------|
| Admin | `admin` | `password` |
| Test User | `user` | `password` |

### 9.3 Key Config
| Config | Env Var | Default |
|--------|---------|---------|
| App locale | `APP_LOCALE` | `de` (German default; supported: `en`, `de`) |
| Media sync path | `MEDIA_SYNC_PATH` | `storage/app/media-sync` |
| Thumbnail max size (KB) | `THUMBNAIL_MAX_SIZE` | `2048` |

### 9.4 Artisan Commands
```bash
php artisan media:sync                           # Sync all media
php artisan media:sync --course=1                 # Sync for a specific course
php artisan media:sync --course=1 --folder=5      # Sync for a specific folder
```

### 9.5 Seed Data
The `DatabaseSeeder` creates (idempotent via `firstOrCreate`):

| Course | Slug | Published | Homepage | Top-level Folders |
|--------|------|-----------|----------|-------------------|
| Introduction to Web Development | `introduction-to-web-development` | Yes | Yes | HTML & CSS, JavaScript, Frameworks, Backend Basics (11 folders total with sub-folders) |
| Data Science Fundamentals | `data-science-fundamentals` | Yes | Yes | Python for DS, Statistics, Machine Learning (8 folders total) |
| Mobile App Development | `mobile-app-development` | No | No | iOS, Android, Cross-Platform (8 folders total) |

Each course has 2-3 levels of nested folders. YouTube videos are seeded into Web Dev (3 videos) and Data Science (2 videos). The "Students" group gets **download** access to Web Dev and **view** access to Data Science. 3 default slideshow images are seeded.

### 9.5.1 UserSeeder
- Seeds 150 realistic users (unique names, usernames, emails) from arrays of first/last names
- 3 admin users, 10 inactive users, rest are active regular users
- Idempotent: skips if `User::count() > 10`

### 9.6 Key File Locations
| Purpose | Path |
|---------|------|
| Models | `app/Models/` |
| Controllers | `app/Http/Controllers/` (subdirs: Admin/, Auth/, User/) |
| Form Requests | `app/Http/Requests/` |
| Policies | `app/Policies/` |
| Services | `app/Services/` |
| Middleware | `app/Http/Middleware/AdminMiddleware.php` |
| Blade Views | `resources/views/` (subdirs: admin/, user/, auth/, components/, layouts/) |
| Routes | `routes/web.php` |
| Seeders | `database/seeders/DatabaseSeeder.php` |
| Translations | `lang/en/` and `lang/de/` |
| Config | `config/media.php` |
| CSS | `resources/css/app.css` (Tailwind v4 with `@source` directive) |
| JS | `resources/js/app.js` (Alpine.js) |
| Vite Config | `vite.config.js` |

---

## 10. Known Behaviors & Edge Cases

### 10.1 Alpine.js Restrictions
- **Do NOT use `<template x-for>`** — causes `Cannot read properties of undefined (reading 'cloneNode')` error in Alpine with Vite build
- **Do NOT use `<template x-if>`** — same error
- Instead, use individual elements with `x-show` or dynamic `:d` attributes for SVGs

### 10.2 Flash Prevention
- No `x-cloak` on admin/user layouts (causes white screen while Alpine/Vite loads)
- Mobile sidebar elements now use `x-cloak` (was previously CSS `display: none` via `data-mobile-sidebar` attribute, which was removed because it overrode Alpine's `x-show`)
- Desktop sidebar uses inline structure in the layout for immediate visibility

### 10.3 Localization
- Translation files in `lang/en/` and `lang/de/` for each language
- App-specific strings in `messages.php` (~320+ keys per language); framework strings in `auth.php`, `pagination.php`, `passwords.php`, `validation.php`
- To add a new language: create a new directory `lang/{code}/`, copy files from `en/`, translate all values
- Use `__('messages.key_name')` in Blade views; `:attribute` placeholders in validation strings are replaced automatically
- **All Blade views fully translated**: every button, label, header, placeholder, empty state, confirm dialog, and table header uses `__('messages.xxx')` — no hardcoded English remains
- **All controller flash messages translated**: all `->with('success', ...)`, `->with('error', ...)`, `->withErrors(...)` in controllers replaced with `__('messages.msg_*')` — covers course, folder, user, group, YouTube, slideshow, media file, login, and password CRUD/action flash messages; ~40+ `msg_*` keys added to both en and de files
- Pagination labels are translatable: `first`, `last`, `showing`, `to`, `of`, `results`, `goto_page`, `navigation` keys in `lang/*/pagination.php`
- Missing `messages.courses` key was added to both `en` and `de` (was causing `messages.courses` fallback to appear in views)

### 10.4 Legacy Password Migration
- Users with `old_password` set can log in using their legacy credentials
- The authentication flow: bcrypt check → legacy SHA1 check → migrate to bcrypt
- On successful legacy login, `old_password` is cleared and the password is hashed with bcrypt
- Any password change (admin reset, user self-change, admin edit) clears `old_password`
- `last_login_at` is recorded on every successful authentication (both bcrypt and legacy paths) via `$user->update(['last_login_at' => now()])`

### 10.5 YouTubeVideo Table Name
- The model has `$table = 'youtube_videos'` to override Laravel's automatic pluralization which would produce `you_tube_videos`

### 10.6 Permission Chain
- `canViewCourse()` checks via `accessibleCourses()` which includes:
  1. Course-level group access (`group_course`)
  2. Folder-level group access for any folder in the course (`group_folder` via `folders` → `groups` → `users`)
- `accessibleFolderIdsInCourse()` includes ancestor folders so users can navigate to deeply nested permitted folders

### 10.7 Folder Count
- Admin courses index uses `withCount('folders')` which counts only top-level folders (the `folders()` relationship has `->whereNull('parent_id')`)
- Homepage also uses `folders_count` (via `withCount`)

### 10.8 Unchecked Checkbox Handling
- All boolean form fields use hidden `<input type="hidden" name="field" value="0">` before the checkbox so `0` is submitted when unchecked
- `old()` helper with default handles first-load vs re-submission correctly

### 10.9 Confirm Dialog Pattern
- Replaces all native `confirm()` calls with an Alpine-powered modal component (`<x-confirm-dialog />`)
- Global Alpine component listens for `confirm-open` window event
- Dispatches form submission (POST with CSRF + method override) or navigates via `isLink` flag
- CSRF token baked in at render time; Cancel/Confirm buttons with `cursor-pointer` class

### 10.10 Primary Color Override
- `--color-indigo-*` CSS variables overridden in `@theme` block so all existing `indigo-*` classes render in `#982315` dark red
- Also added `--color-primary-*` palette for future semantic use
- CSS in `resources/css/app.css`

### 10.11 Folder Accordion
- Uses `@alpinejs/collapse` plugin for smooth expand/collapse animations
- Pattern: `x-data="{ open: false }"` with `x-show` + `x-collapse` on folder content
- Action buttons use `@click.stop` to prevent accordion toggle
- Courses with many folders use search + accordion + pagination (20 per page) in both admin and user views

### 10.12 Folder Sticky Feature
- `is_sticky` boolean column on `folders` table
- Toggle via dedicated `folders/{folder}/toggle-sticky` route (pins/unpins)
- Sticky folders sort first (`orderBy('is_sticky', 'desc')->orderBy('sort_order')`) in all queries via `scopeOrdered`
- Pin/unpin button (bookmark icon) shown on folder cards; sticky badge displayed

### 10.13 Course Materials
- Renamed from "Uncategorized Content" to "Course Materials" (`messages.uncategorized_content`)
- Represents content with `folder_id = null` (no folder assigned)
- Displayed first (above folder search/accordion) in both admin and user course show views

### 10.14 User Index Filters
- Admin user index (`/admin/users`) supports search by name, username, email
- Status filter: Active / Inactive / All

### 10.15 Input Field Consistency
- All input, textarea, and select elements use `text-base px-4 py-3` classes across all admin and user forms

### 10.16 Pagination Customization
- Customized `resources/views/vendor/pagination/tailwind.blade.php` adds first/last page buttons (double-chevron SVGs)
- Pagination labels are translatable via `lang/*/pagination.php`

### 10.17 Normal User `/courses` Redirect
- `Route::get('courses', fn() => redirect()->route('dashboard'))` prevents `courses.index` route being accessed by normal users (only exists as admin route)

---

## 11. File Tree (Key Directories)

```
app/
├── Console/Commands/SyncMedia.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── ActivityLogController.php
│   │   │   ├── CourseController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FolderController.php
│   │   │   ├── MediaController.php
│   │   │   ├── PermissionController.php
│   │   │   ├── SlideshowImageController.php
│   │   │   ├── UserController.php
│   │   │   └── YouTubeController.php
│   │   ├── Auth/LoginController.php
│   │   ├── User/
│   │   │   ├── CourseController.php
│   │   │   ├── DashboardController.php
│   │   │   └── ProfileController.php
│   │   ├── Controller.php
│   │   ├── HomeController.php
│   │   └── MediaStreamController.php
│   ├── Middleware/AdminMiddleware.php
│   ├── Middleware/SetLocale.php
│   ├── Middleware/CheckUserActiveSession.php (via bootstrap/app.php)
│   └── Requests/
│       ├── StoreCourseRequest.php
│       ├── StoreFolderRequest.php
│       ├── StoreMediaRequest.php
│       ├── StoreUserRequest.php
│       ├── StoreYouTubeRequest.php
│       ├── UpdateCourseRequest.php
│       └── UpdateUserRequest.php
├── Models/
│   ├── ActivityLog.php
│   ├── Course.php
│   ├── Folder.php
│   ├── Group.php
│   ├── MediaFile.php
│   ├── Role.php
│   ├── SlideshowImage.php
│   ├── User.php
│   └── YouTubeVideo.php
├── Policies/
│   ├── CoursePolicy.php
│   ├── FolderPolicy.php
│   ├── MediaFilePolicy.php
│   ├── UserPolicy.php
│   └── YouTubeVideoPolicy.php
└── Services/
    ├── ActivityLoggerService.php
    ├── FileStorageService.php
    └── MediaSyncService.php

resources/views/
├── admin/
│   ├── activity-logs/index.blade.php
│   ├── courses/
│   │   ├── _folder_contents.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── dashboard.blade.php
│   ├── folders/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── index.blade.php
│   ├── media/
│   │   ├── create.blade.php
│   │   └── index.blade.php
│   ├── permissions/index.blade.php
│   ├── slideshow-images/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── index.blade.php
│   ├── users/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── index.blade.php
│   └── youtube/
│       ├── create.blade.php
│       ├── edit.blade.php
│       └── index.blade.php
├── auth/login.blade.php
├── components/
│   ├── audio-player.blade.php
│   ├── breadcrumbs.blade.php
│   ├── confirm-dialog.blade.php
│   ├── empty-state.blade.php
│   ├── searchable-multi-select.blade.php
│   ├── searchable-select.blade.php
│   ├── sidebar-nav-items.blade.php
│   ├── sidebar.blade.php
│   ├── toast.blade.php
│   └── top-nav.blade.php
├── layouts/
│   ├── admin.blade.php
│   ├── app.blade.php
│   └── user.blade.php
├── user/
│   ├── courses/
│   │   ├── _folder_contents.blade.php
│   │   └── show.blade.php
│   └── dashboard.blade.php
└── welcome.blade.php

lang/
├── en/
│   ├── auth.php
│   ├── messages.php
│   ├── pagination.php
│   ├── passwords.php
│   └── validation.php
└── de/
    ├── auth.php
    ├── messages.php
    ├── pagination.php
    ├── passwords.php
    └── validation.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php        (roles, users, sessions)
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2024_01_01_000001_create_courses_table.php
│   ├── 2024_01_01_000002_create_folders_table.php
│   ├── 2024_01_01_000003_create_media_files_table.php
│   ├── 2024_01_01_000004_create_youtube_videos_table.php
│   ├── 2024_01_01_000005_create_course_user_table.php  (legacy)
│   ├── 2024_01_01_000006_create_folder_user_table.php  (legacy)
│   ├── 2024_01_01_000007_create_activity_logs_table.php
│   ├── 2024_01_01_000008_add_username_to_users_table.php
│   ├── 2024_01_01_000010_create_groups_table.php
│   ├── 2024_01_01_000011_create_group_user_table.php
│   ├── 2024_01_01_000012_create_group_course_table.php
│   ├── 2024_01_01_000013_create_group_folder_table.php
│   ├── 2026_07_04_135923_add_show_on_homepage_to_courses_table.php
│   ├── 2026_07_05_102355_add_old_password_to_users_table.php
│   ├── 2026_07_05_135000_create_slideshow_images_table.php
│   └── 2026_07_05_140000_add_is_sticky_to_folders_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php
```

---

## 12. Project Milestones (Build Order)

1. **Scaffold**: Laravel + Tailwind v4 + Alpine.js + Vite
2. **Auth**: migrations (users, roles, sessions), LoginController, AdminMiddleware
3. **Admin layout**: sidebar, top-nav, app layout (sidebar flash fix)
4. **Course CRUD**: migration, model, controller, views (create, edit, index, show with folders)
5. **Folder CRUD**: nested hierarchy, reorder, recursive view
6. **Media upload/stream**: FileStorageService, protected routes, audio player
7. **YouTube Videos**: CRUD, display
8. **User management**: CRUD, password reset, role assignment
9. **Permission system**: groups, group-based course/folder access
10. **User-facing views**: dashboard, course viewer with access filtering
11. **Activity logging**: ActivityLoggerService, admin viewer
12. **Media sync**: SyncMedia command, MediaSyncService
13. **Homepage**: hero slideshow, course cards, guest/authenticated views
14. **UI polish**: overflow fixes, mobile responsiveness, Alpine hydration fixes
15. **Localization**: all views translated (en/de), SetLocale middleware, German default
16. **Slideshow admin**: CRUD for hero slideshow images, database-driven display
17. **UserSeeder**: 150 realistic users, idempotent seeding
18. **Confirm dialog**: Alpine-powered modal replacing native confirm()
19. **Folder sticky**: pin/unpin folders (is_sticky), sticky-first sorting
20. **Pagination**: first/last buttons, translatable labels
21. **User index filters**: search + status filter
22. **Input standardization**: consistent text-base px-4 py-3 across all forms
23. **Folder accordion**: @alpinejs/collapse, search, pagination for folder-heavy courses
24. **Primary color**: changed to #982315 dark red via CSS variable override
