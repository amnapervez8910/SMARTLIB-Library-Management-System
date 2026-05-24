# SmartLib Documentation

## Overview
SmartLib is a role-based library management web application for Students, Faculty, and Librarians. It provides authentication, panel-specific dashboards, book browsing and issuing flows, and administrative features for librarians.

## Folder Structure
- `student/` — Student panel pages (dashboard, browse, my books, request, return, profile)
- `faculty/` — Faculty panel pages (dashboard, recommendations, requests, notifications, reports, issued books)
- `librarian/` — Librarian panel pages (dashboard and admin features)
- `assets/styles/` — Centralized CSS themes and layout (`theme.css`, `layout.css`, `student.css`, `index.css`)
- `partials/` — Reusable PHP includes like `sidebar.php`
- `config/` — Shared configuration (`db.php` for database connection)
- Root — Entrypoints like `index.html`, `login.html`, `login.php`, `logout.php`
- `documentation/` — Project docs (this file)

## Authentication and Roles
- Login form posts to `login.php`, which authenticates using prepared queries and sets session variables.
- Role-based redirects:
  - Student → `student/student.php`
  - Faculty → `faculty/faculty_dashboard.php`
  - Librarian → `librarian/librarian-dashboard.php`
- Session variables used:
  - `$_SESSION['member_id']`, `$_SESSION['name']`, `$_SESSION['email']`, `$_SESSION['role']`
  - Defined in `login.php`

## Navigation and Reusability
- Shared sidebar across panels: `partials/sidebar.php`
  - Detects current panel via `$_SERVER['PHP_SELF']` and renders appropriate links
  - Student menu used in `student/*` pages
  - Librarian menu used in `librarian/*` pages
  - Faculty menu used in `faculty/*` pages
  - Logout button routes to correct path depending on panel

## Styling
- `assets/styles/theme.css` — Global design tokens (colors, shadows, radius)
- `assets/styles/layout.css` — Sidebar, main content layout, cards, responsiveness
- `assets/styles/student.css` — Student panel-specific enhancements
- `assets/styles/index.css` — Landing page styles

## Database
- `config/db.php` — Centralized connection using `mysqli`
- Some legacy pages may still include `db_connect.php` or `SmartLib.php`. Migration to `config/db.php` is recommended for consistency.

## Key Pages
- `index.html` — Landing page for SmartLib
- `login.html` — Login form (posts to `login.php`)
- `login.php` — Authentication and role-based redirection
- `logout.php` — Clears session and returns to login
- Panel pages:
  - Student: `student/student.php`, `student/browse.php`, `student/mybooks.php`, `student/request.php`, `student/return.php`, `student/profile.php`
  - Faculty: `faculty/faculty_dashboard.php`, `faculty/recommend_book.php`, `faculty/faculty_requests.php`, `faculty/faculty_notifications.php`, `faculty/faculty_reports.php`, `faculty/issue_books.php`
  - Librarian: `librarian/librarian-dashboard.php` (plus `.html` admin utilities)

## Recent Cleanup
- Removed duplicate root pages for panels to avoid inconsistencies and broken links.
- Consolidated navigation via `partials/sidebar.php` across panels.
- Removed non-functional JavaScript redirections from `login.html` (server-side `login.php` now handles routing).

## Running Locally
- Use a local PHP server or stack (e.g., XAMPP, WAMP, or PHP built-in server):
  - From the project root: `php -S localhost:8000 -t c:\Users\mariy\OneDrive\Desktop\SMARTLIB`
  - Ensure database credentials in `config/db.php` match your local setup.

## Development Guidelines
- Prefer shared includes for common UI (e.g., `partials/sidebar.php`) to reduce duplication.
- Use centralized styles under `assets/styles/` for consistency.
- When adding new panel pages:
  - Place them inside the relevant folder (`student/`, `faculty/`, `librarian/`)
  - Include `partials/sidebar.php`
  - Use `config/db.php` for database access

## Notes
- If migrating legacy pages, verify includes and relative paths (e.g., `../` from panel folders) and ensure session checks remain intact per role.

