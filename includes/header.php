<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e(STORE_NAME) ?></title>

<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php if (is_logged_in()): ?>
<header class="top no-print">

    <div class="brand">
        <img src="assets/images/omar-logo.webp"
             alt="Omar Grocery Logo"
             class="store-logo">

        <span class="store-name">
            <?= e(STORE_NAME) ?>
            <span class="brand-separator">—</span>
            <span class="brand-subtitle">Listahan ng Utang</span>
        </span>
    </div>

    <!-- Desktop Navigation -->
    <nav class="top-nav">
        <a href="change_password.php">Change Password</a>
        <a href="logout.php">Log out</a>
    </nav>

    <!-- Mobile Hamburger Button -->
    <button class="menu-toggle"
            type="button"
            aria-label="Open menu"
            aria-expanded="false"
            onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>

</header>

<!-- Mobile Menu -->
<nav class="mobile-menu no-print" id="mobileMenu">
    <a href="change_password.php">Change Password</a>
    <a href="logout.php" class="mobile-logout">Log out</a>
</nav>
<?php endif; ?>

<div class="wrap">
