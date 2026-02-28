# Travisa PHP Project

PHP version of the Travisa site with database **swiis_db** (localhost), admin login, and management for testimonials and blogs.

## Requirements

- PHP 7.4+ with PDO MySQL
- MySQL/MariaDB (localhost)
- Web server (Apache with mod_rewrite, or PHP built-in server)

## Setup

1. **Database**
   - Create database `swiis_db` on localhost (or run the one-time installer).
   - **One-time install:** Open in browser: `http://localhost/Travisa/install/install.php`
   - This creates the database, tables (`admins`, `testimonials`, `blogs`), and a default admin user.

2. **Database config**
   - Edit `config/database.php` if your MySQL user/password differ (default: `root` / no password).

3. **Default admin login**
   - URL: **`/admin`** or **`/admin/`** (e.g. `http://localhost/Travisa/admin/`)
   - Username: **admin**
   - Password: **admin123**
   - Change the password after first login (e.g. by updating the `admins` table or adding a “change password” feature).

4. **Security**
   - After running the installer, delete or protect the `install/` folder (e.g. remove it or deny access in the server config).

## Routes / URLs

| URL | Description |
|-----|-------------|
| `/` or `/index.php` | Home (dynamic testimonials from DB) |
| `/testimonial.php` | Testimonials page (from DB) |
| `/blog.php` | Blog listing (published posts only) |
| `/blog-single.php?slug=your-post-slug` | Single blog post |
| **/admin** or **/admin/** | Admin login |
| `/admin/dashboard.php` | Admin dashboard (after login) |
| `/admin/testimonials.php` | Manage testimonials (add, edit, delete, publish/unpublish) |
| `/admin/blogs.php` | Manage blogs (add, edit, delete, publish/unpublish) |

## Admin features

- **Login** at `/admin` (session-based).
- **Dashboard:** Counts of testimonials and blogs, quick links to manage them.
- **Testimonials:** Add, edit, delete, publish, unpublish. Fields: author name, profession, content, image path, rating (1–5).
- **Blogs:** Add, edit, delete, publish, unpublish. Fields: title, slug, excerpt, content, featured image. Slug can be auto-generated from the title.

## Project structure

```
Travisa/
├── config/database.php      # DB connection (swiis_db, localhost)
├── includes/
│   ├── auth.php             # Admin session helpers
│   ├── testimonials_section.php  # Outputs testimonial carousel from DB
│   ├── blog_header.php      # Shared header for blog pages
│   └── blog_footer.php      # Shared footer + scripts
├── admin/
│   ├── index.php            # Login page
│   ├── dashboard.php       # Dashboard after login
│   ├── logout.php
│   ├── testimonials.php     # List testimonials
│   ├── testimonial-edit.php # Add/Edit testimonial
│   ├── blogs.php            # List blogs
│   └── blog-edit.php        # Add/Edit blog
├── install/install.php      # One-time DB + admin setup
├── sql/schema.sql           # Optional: run manually instead of install.php
├── index.php                # Home (dynamic testimonials)
├── testimonial.php          # Testimonials page
├── blog.php                 # Blog listing
├── blog-single.php         # Single blog post
├── .htaccess                # Redirects /admin to /admin/
└── (existing: css/, js/, lib/, img/, *.html)
```

## Running locally

**Apache:** Point the document root to the project folder; ensure `mod_rewrite` is enabled for `.htaccess`.

**PHP built-in server:**
```bash
cd Travisa
php -S localhost:8000
```
Then open `http://localhost:8000` and `http://localhost:8000/admin/`.  
Note: The built-in server does not process `.htaccess`; use `http://localhost:8000/admin/index.php` if `/admin/` does not show the login page.
