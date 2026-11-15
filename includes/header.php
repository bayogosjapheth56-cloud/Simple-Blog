<?php
// includes/header.php - shared top of every page
$pageTitle = $pageTitle ?? "Blogify";
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Instant Theme Loader (prevents flash of white/dark screen) -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('blog_theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>

<body class="app-body">

    <div class="py-4 py-md-5">
        <div class="container app-container">
            <div class="app-card shadow-sm">
                <!-- App Navbar -->
                <nav class="navbar navbar-expand app-navbar">
                    <div class="container-fluid px-0">
                        <a class="navbar-brand text-white d-flex align-items-center" href="index.php">
                            <i class="bi bi-app me-2"></i>
                            <span>Blogify</span>
                        </a>
                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <a class="nav-link-custom" href="index.php">All Posts</a>

                            <!-- Light / Dark Mode Toggle Switch (Reference Photo) -->
                            <button class="theme-switch" id="themeToggle" type="button" role="switch"
                                aria-label="Toggle light and dark mode" title="Toggle light and dark mode">
                                <span class="theme-switch-track">
                                    <span class="theme-switch-thumb">
                                        <i class="bi bi-brightness-high-fill icon-sun"></i>
                                        <i class="bi bi-moon-fill icon-moon"></i>
                                    </span>
                                </span>
                            </button>

                            <a class="btn btn-nav-new-post" href="create.php">+ New Post</a>
                        </div>
                    </div>
                </nav>

                <!-- App Inner Content -->
                <div class="app-content">