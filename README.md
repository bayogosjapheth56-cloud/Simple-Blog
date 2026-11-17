# Blogify – Blog & Post Management System

A web-based CRUD application for writing, organising and publishing blog posts.
Built with **PHP**, **MySQL**, **HTML/CSS** and **Bootstrap 5** for the Web Systems Technologies final project.

**Live site:** japheth.lovestoblog.com

## Features

- **Create, read, update and delete** blog posts
- **Content Board**: posts split into *Drafts* and *Published* columns, with totals
- **All Posts table** with view / edit / delete actions
- **Categories** stored in their own table, related to posts (one category → many posts).
  Pick an existing category or type a new one.
- **Featured photo upload** with live preview (JPG, PNG, GIF, WebP, max 5MB, content checked to be a real image)
- **Search** (instant as you type, plus server-side search) and **category filter**
- **Light / dark mode** switch (remembered by the browser)
- **Delete confirmation modal**
- **Security**: prepared statements (SQL injection), `htmlspecialchars` output (XSS),
  CSRF tokens on all forms, delete only via POST, no raw database errors shown to visitors

## Database design

| Table | Fields |
|---|---|
| `categories` | `id` (INT, PK, auto-increment), `name` (VARCHAR 50, unique) |
| `posts` | `id` (INT, PK, auto-increment), `title` (VARCHAR 150), `author` (VARCHAR 100), `category_id` (INT, FK → `categories.id`), `content` (TEXT), `image` (VARCHAR 255, nullable), `status` (ENUM Draft/Published), `created_at` (TIMESTAMP), `updated_at` (TIMESTAMP) |

Relationship: `posts.category_id` → `categories.id` (many-to-one). A category that is
still used by a post cannot be deleted (`ON DELETE RESTRICT`).

## Project structure

```
index.php                  Content Board + All Posts table (SELECT, search, filter)
create.php                 Add a post (INSERT)
view.php                   Read one post (SELECT)
edit.php                   Edit a post (UPDATE)
delete.php                 Delete a post (DELETE, POST + CSRF only)
db_connect.php             Database connection settings
database.sql               Database schema + sample data
includes/header.php        Shared page header, navbar, theme styles
includes/footer.php        Shared footer, delete modal, theme script
includes/functions.php     Session, CSRF, validation and category helpers
includes/upload_helper.php Photo upload validation/saving
includes/post_card.php     Board card partial
includes/category_datalist.php  Category suggestions for forms
assets/                    Logo / favicon
uploads/                   Uploaded post photos
```

## Run locally (XAMPP)

1. Copy the project folder into `C:\xampp\htdocs\blog_system`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) → **Import** → choose `database.sql`.
4. Visit `http://localhost/blog_system/`.

## Deploy (InfinityFree)

1. In the InfinityFree control panel, open **MySQL Databases** and create a database.
2. Edit `database.sql` and **delete the `CREATE DATABASE` and `USE` lines** at the top.
3. Open InfinityFree's phpMyAdmin, select your database and **import** `database.sql`.
4. Put the InfinityFree hostname, username, password and database name in `db_connect.php`.
5. Upload all project files to `htdocs/` with the File Manager (include the empty `uploads/` folder).
6. Open your live URL and test adding, viewing, editing and deleting a post.



Japheth Bayogos BSIT 3A-SD
