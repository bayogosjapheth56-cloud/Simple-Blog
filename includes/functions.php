<?php
// includes/functions.php - shared helpers (session, CSRF protection, categories)
// Must be included before any HTML output, because it starts the session.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- CSRF protection ---------- */

// Returns the per-session CSRF token, creating it on first use
function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

// Hidden form field that carries the CSRF token
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// True when the submitted token matches the session token
function csrf_valid(): bool
{
    $sent = $_POST["csrf_token"] ?? "";
    return is_string($sent) && $sent !== "" && hash_equals(csrf_token(), $sent);
}

/* ---------- Validation ---------- */

// Checks the text fields of a post form; returns a list of error messages
function validate_post_fields(string $title, string $author, string $category, string $content): array
{
    $errors = [];
    if ($title === "")
        $errors[] = "Title is required.";
    elseif (mb_strlen($title) > 150)
        $errors[] = "Title must be 150 characters or less.";

    if ($author === "")
        $errors[] = "Author is required.";
    elseif (mb_strlen($author) > 100)
        $errors[] = "Author must be 100 characters or less.";

    if ($category === "")
        $errors[] = "Category is required.";
    elseif (mb_strlen($category) > 50)
        $errors[] = "Category must be 50 characters or less.";

    if ($content === "")
        $errors[] = "Content is required.";

    return $errors;
}

/* ---------- Categories ---------- */

// SQL used everywhere a post is read, so "category" comes from the categories table
const POST_SELECT_SQL = "SELECT p.*, c.name AS category
                         FROM posts p
                         JOIN categories c ON c.id = p.category_id";

// Returns all categories (id, name) sorted by name, for form suggestions
function get_all_categories(mysqli $conn): array
{
    $rows = [];
    $res = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = $r;
        }
    }
    return $rows;
}

// Finds a category by name (case-insensitive) or creates it; returns its id (null on failure)
function get_or_create_category(mysqli $conn, string $name): ?int
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE name = ?");
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($row) {
        return (int) $row["id"];
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO categories (name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $name);
    if (mysqli_stmt_execute($stmt)) {
        return (int) mysqli_insert_id($conn);
    }
    error_log("Category insert failed: " . mysqli_error($conn));
    return null;
}
