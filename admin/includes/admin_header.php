<?php
/**
 * Admin — Header partageé (inclus dans chaque page admin)
 */
$currentUser = get_logged_in_user();
$currentPage = basename($_SERVER['SCRIPT_FILENAME'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin', ENT_QUOTES | ENT_HTML5) ?> — H&M Immobilier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
    <?php if (isset($extraCss)): echo $extraCss; endif; ?>
</head>
<body>

<!-- ── Sidebar ──────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-wrap">
            <i class="fas fa-building"></i>
            <span class="logo-text">H&M <em>Immobilier</em></span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Fermer sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="nav-section-label">Principal</li>
            <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <a href="/admin/dashboard.php"><i class="fas fa-th-large"></i><span>Tableau de bord</span></a>
            </li>
            <li class="<?= in_array($currentPage, ['properties','property-form']) ? 'active' : '' ?>">
                <a href="/admin/properties.php"><i class="fas fa-home"></i><span>Biens Immobiliers</span></a>
            </li>
            <li class="<?= $currentPage === 'property-form' && !isset($_GET['edit']) ? 'active' : '' ?>">
                <a href="/admin/property-form.php"><i class="fas fa-plus-circle"></i><span>Ajouter un Bien</span></a>
            </li>

            <?php if (is_admin()): ?>
            <li class="nav-section-label">Administration</li>
            <li class="<?= $currentPage === 'users' || ($currentPage === 'user-form' && isset($_GET['edit'])) ? 'active' : '' ?>">
                <a href="/admin/users.php"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
            </li>
            <li class="<?= $currentPage === 'user-form' && !isset($_GET['edit']) ? 'active' : '' ?>">
                <a href="/admin/user-form.php"><i class="fas fa-user-plus"></i><span>Créer Utilisateur</span></a>
            </li>
            <li class="<?= $currentPage === 'audit' ? 'active' : '' ?>">
                <a href="/admin/audit.php"><i class="fas fa-shield-alt"></i><span>Journal Audit</span></a>
            </li>
            <?php endif; ?>

            <li class="nav-section-label">Compte</li>
            <li class="<?= $currentPage === 'profile' ? 'active' : '' ?>">
                <a href="/admin/profile.php"><i class="fas fa-user-circle"></i><span>Mon Profil</span></a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($currentUser['email'], 0, 2)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($currentUser['email'], ENT_QUOTES | ENT_HTML5) ?></span>
            <span class="user-role <?= $currentUser['role'] === 'admin' ? 'role-admin' : 'role-agent' ?>"><?= ucfirst($currentUser['role']) ?></span>
        </div>
        <a href="/admin/logout.php" class="logout-btn" title="Déconnecter"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</aside>

<!-- ── Overlay mobile ───────────────────────────────────── -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── Main Content ─────────────────────────────────────── -->
<main class="main-content" id="mainContent">

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-menu-btn" id="topbarMenuBtn" aria-label="Menu"><i class="fas fa-bars"></i></button>
            <h1 class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES | ENT_HTML5) ?></h1>
        </div>
        <div class="topbar-right">
            <a href="/index.php" target="_blank" class="topbar-site-link"><i class="fas fa-globe"></i><span>Voir le site</span></a>
            <div class="topbar-avatar"><?= strtoupper(substr($currentUser['email'], 0, 2)) ?></div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash flash-success" id="flashMsg">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES | ENT_HTML5) ?></span>
        <button class="flash-close">&times;</button>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="flash flash-error" id="flashMsg">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES | ENT_HTML5) ?></span>
        <button class="flash-close">&times;</button>
    </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- Content area -->
    <div class="content-area">
