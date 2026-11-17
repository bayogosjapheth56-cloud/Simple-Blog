<?php
// includes/header.php - shared top of every page (Purple UI/UX)
$pageTitle = $pageTitle ?? "Blogify";
$activeNav = $activeNav ?? "board";

// Fetch categories that have at least one post, for the navbar dropdown
$navCategories = [];
if (isset($conn)) {
    $navCatRes = mysqli_query($conn, "SELECT DISTINCT c.name
                                      FROM categories c
                                      JOIN posts p ON p.category_id = c.id
                                      ORDER BY c.name ASC");
    if ($navCatRes) {
        while ($nc = mysqli_fetch_assoc($navCatRes)) {
            $navCategories[] = $nc["name"];
        }
    }
}
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
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo.png?v=2">

    <!-- Purple UI/UX System Theme -->
    <style>
        :root {
            --primary-purple: #2d1558;
            --primary-purple-hover: #220e45;
            --accent-purple: #6f38d5;
            --accent-purple-hover: #5d2dbd;
            --light-lavender: #eae6f4;
            --badge-cat-bg: #eee8fc;
            --badge-cat-text: #532ca8;
        }

        [data-bs-theme="dark"] {
            --primary-purple: #1e0d3d;
            --accent-purple: #8e55f7;
            --light-lavender: #231c36;
            --badge-cat-bg: #35215c;
            --badge-cat-text: #d8c7ff;
        }

        body {
            background-color: #f5f3fa !important;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        [data-bs-theme="dark"] body {
            background-color: #120e1e !important;
        }

        .bg-purple-header {
            background-color: var(--primary-purple) !important;
        }

        .bg-purple-accent {
            background-color: var(--accent-purple) !important;
            color: #ffffff !important;
        }

        .btn-purple {
            background-color: var(--accent-purple) !important;
            color: #ffffff !important;
            border: none !important;
            transition: all 0.15s ease-in-out;
        }

        .btn-purple:hover {
            background-color: var(--accent-purple-hover) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(111, 56, 213, 0.3);
        }

        .btn-outline-purple {
            border: 1px solid var(--accent-purple) !important;
            color: var(--accent-purple) !important;
            background: transparent !important;
            transition: all 0.15s ease-in-out;
        }

        .btn-outline-purple:hover {
            background-color: var(--accent-purple) !important;
            color: #ffffff !important;
        }

        .badge-category {
            background-color: var(--badge-cat-bg) !important;
            color: var(--badge-cat-text) !important;
            font-weight: 600;
        }

        .card-avatar-purple {
            background-color: #ece6fa !important;
            color: #532ca8 !important;
        }

        [data-bs-theme="dark"] .card-avatar-purple {
            background-color: #35215c !important;
            color: #d8c7ff !important;
        }

        .board-col-drafts {
            background-color: #eae6f4 !important;
        }

        [data-bs-theme="dark"] .board-col-drafts {
            background-color: #1e1930 !important;
            border: 1px solid #2d2644 !important;
        }

        .board-col-published {
            background-color: #eaf6ee !important;
            border: 1px solid #d4edd9 !important;
        }

        [data-bs-theme="dark"] .board-col-published {
            background-color: #15271d !important;
            border: 1px solid #1f3b29 !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-purple) !important;
            box-shadow: 0 0 0 0.25rem rgba(111, 56, 213, 0.2) !important;
        }

        /* "Add photo" Button matching reference image 1 */
        .btn-add-photo {
            background-color: #946ef2 !important;
            color: #ffffff !important;
            border-radius: 14px !important;
            padding: 10px 24px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(148, 110, 242, 0.35);
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none !important;
        }

        .btn-add-photo:hover {
            background-color: #8356ed !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(148, 110, 242, 0.45);
            color: #ffffff !important;
        }

        /* Modern Toggle Switch matching Reference Image 3 */
        .theme-switch-wrapper {
            display: inline-flex;
            align-items: center;
        }

        .custom-theme-switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 26px;
            margin: 0;
            cursor: pointer;
        }

        .custom-theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 30px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 2px;
            bottom: 2px;
            background-color: #ffffff;
            border-radius: 50%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        input:checked+.switch-slider {
            background-color: #946ef2;
            border-color: #946ef2;
        }

        input:checked+.switch-slider:before {
            transform: translateX(20px);
        }

        /* Separate Action Buttons matching Reference Image 3 */
        .btn-action-view {
            color: #6f38d5 !important;
            border: 1px solid rgba(111, 56, 213, 0.3) !important;
            background: transparent !important;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            transition: all 0.15s ease-in-out;
        }

        .btn-action-view:hover {
            background-color: #f3effc !important;
            border-color: #6f38d5 !important;
            color: #532ca8 !important;
        }

        [data-bs-theme="dark"] .btn-action-view:hover {
            background-color: #2b1f48 !important;
            color: #c4b5fd !important;
        }

        .btn-action-edit {
            color: #6c757d !important;
            border: 1px solid rgba(108, 117, 125, 0.3) !important;
            background: transparent !important;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            transition: all 0.15s ease-in-out;
        }

        .btn-action-edit:hover {
            background-color: #f8f9fa !important;
            border-color: #6c757d !important;
            color: #495057 !important;
        }

        [data-bs-theme="dark"] .btn-action-edit:hover {
            background-color: #262730 !important;
            color: #adb5bd !important;
        }

        .btn-action-delete {
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.3) !important;
            background: transparent !important;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            transition: all 0.15s ease-in-out;
        }

        .btn-action-delete:hover {
            background-color: #fee2e2 !important;
            border-color: #ef4444 !important;
            color: #b91c1c !important;
        }

        [data-bs-theme="dark"] .btn-action-delete:hover {
            background-color: #3b1919 !important;
            color: #fca5a5 !important;
        }
        .navbar-toggler {
            border: none !important;
            padding: 0.25rem 0.5rem !important;
            color: #ffffff !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .navbar-toggler:focus {
            box-shadow: none !important;
            outline: none !important;
        }

        @media (max-width: 991.98px) {
            .mobile-nav-menu {
                padding-top: 1rem;
                margin-top: 0.75rem;
                border-top: 1px solid rgba(255, 255, 255, 0.15);
            }
            .mobile-nav-link {
                padding: 0.6rem 0.75rem !important;
                border-radius: 8px;
                display: block;
            }
            .mobile-nav-link:hover {
                background: rgba(255, 255, 255, 0.1);
            }
        }
    </style>
</head>

<body>

    <div class="py-2 py-md-4 py-lg-5">
        <div class="container px-2 px-sm-3" style="max-width: 1080px;">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <!-- App Navbar (Responsive with Burger Icon for Mobile/Tablet) -->
                <nav class="navbar navbar-expand-lg bg-purple-header px-3 px-md-4 py-3">
                    <div class="container-fluid px-0">
                        <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2 me-3" href="index.php">
                            <img src="assets/logo.png?v=2" alt="Blogify Logo" width="32" height="32"
                                class="rounded-2 shadow-sm">
                            <span>Blogify</span>
                        </a>

                        <!-- Mobile / Tablet Right Controls (Theme Switch + Burger Icon) -->
                        <div class="d-flex align-items-center gap-2 d-lg-none ms-auto">
                            <div class="theme-switch-wrapper" title="Toggle theme">
                                <label class="custom-theme-switch">
                                    <input type="checkbox" class="themeSwitchInput" aria-label="Toggle theme">
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#blogifyNavbar" aria-controls="blogifyNavbar" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <i class="bi bi-list fs-1 text-white lh-1"></i>
                            </button>
                        </div>

                        <!-- Collapsible Navigation (Desktop inline, Mobile/Tablet drawer) -->
                        <div class="collapse navbar-collapse mobile-nav-menu" id="blogifyNavbar">
                            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3 ms-lg-auto">
                                <a class="mobile-nav-link text-decoration-none px-2 py-1 <?= $activeNav === 'board' ? 'text-white fw-bold border-bottom border-2 border-white' : 'text-white-50' ?>"
                                    href="index.php?view=board">Board</a>
                                <a class="mobile-nav-link text-decoration-none px-2 py-1 <?= $activeNav === 'table' ? 'text-white fw-bold border-bottom border-2 border-white' : 'text-white-50' ?>"
                                    href="index.php?view=table">All Posts</a>

                                <!-- Categories Dropdown in Nav -->
                                <div class="dropdown">
                                    <button
                                        class="mobile-nav-link btn btn-link text-white-50 dropdown-toggle p-0 text-decoration-none border-0 w-100 text-start text-lg-center"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Categories
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li><a class="dropdown-item" href="index.php"><i class="bi bi-grid me-2"></i> All
                                                Categories</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <?php if (!empty($navCategories)): ?>
                                            <?php foreach ($navCategories as $nc): ?>
                                                <li>
                                                    <a class="dropdown-item" href="index.php?category=<?= urlencode($nc) ?>">
                                                        <?= htmlspecialchars($nc) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><span class="dropdown-item text-muted">No categories</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <!-- Light / Dark Mode Toggle Switch on Desktop -->
                                <div class="theme-switch-wrapper d-none d-lg-inline-flex ms-lg-2" title="Toggle theme">
                                    <label class="custom-theme-switch">
                                        <input type="checkbox" class="themeSwitchInput" id="themeSwitch" aria-label="Toggle theme">
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- App Inner Content -->
                <div class="<?= !empty($noContentPadding) ? '' : 'p-3 p-md-4 p-lg-5' ?>">