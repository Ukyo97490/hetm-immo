<?php
/**
 * H&M Immobilier — Nos Biens (public)
 */
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/PropertyModel.php';

$model   = new PropertyModel();
$page    = max(1, sanitize_int($_GET['page'] ?? '1') ?? 1);
$perPage = 6;

$filters   = [];
$typeVal   = $_GET['type']      ?? '';
$searchVal = $_GET['search']    ?? '';
$maxPrice  = sanitize_float($_GET['max_price'] ?? null);

$validTypes = ['maison','appartement','terrain','villa','chalet'];
if (in_array($typeVal, $validTypes, true)) $filters['type']   = $typeVal;
if ($searchVal !== '')                      $filters['search'] = $searchVal;

$result = $model->findPublicAll($filters, $page, $perPage);

if ($maxPrice !== null && $maxPrice > 0) {
    $result['properties'] = array_filter($result['properties'], fn($p) => (float)$p['price'] <= $maxPrice);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H&M Immobilier | Nos Biens</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Header -->
<header class="site-header">
    <nav>
        <div class="logo">H&M <span>Immobilier</span></div>
        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="nos-biens.php" class="active">Nos Biens</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="a-propos.php">À Propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<div class="mobile-menu">
    <div class="mobile-menu-bar">
        <div class="mobile-menu-item"><a href="index.php"><i class="fas fa-home"></i><span>Accueil</span></a></div>
        <div class="mobile-menu-item active"><a href="nos-biens.php"><i class="fas fa-building"></i><span>Nos Biens</span></a></div>
        <div class="mobile-menu-item"><a href="services.php"><i class="fas fa-handshake"></i><span>Services</span></a></div>
        <div class="mobile-menu-item"><a href="a-propos.php"><i class="fas fa-users"></i><span>À Propos</span></a></div>
        <div class="mobile-menu-item"><a href="contact.php"><i class="fas fa-envelope"></i><span>Contact</span></a></div>
    </div>
</div>

<!-- Page Title -->
<section class="page-title">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
        <h1>Nos Biens</h1>
        <p>Parcourez notre sélection de propriétés d'exception, idéalement situées partout en France.</p>
    </div>
</section>

<!-- Filters -->
<section class="filters-section">
    <div class="container">
        <form method="GET" action="nos-biens.php">
            <div class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" value="<?= htmlspecialchars($searchVal, ENT_QUOTES|ENT_HTML5) ?>" placeholder="Recherche (titre, lieu...)">
                </div>
                <div class="filter-group">
                    <select name="type">
                        <option value="">Type de bien</option>
                        <?php foreach (['maison','appartement','terrain','villa','chalet'] as $t): ?>
                        <option value="<?= $t ?>" <?= $typeVal === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="text" name="max_price" value="<?= $maxPrice !== null ? number_format($maxPrice,0,',','') : '' ?>" placeholder="Budget max €">
                </div>
                <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Rechercher</button>
            </div>
        </form>
    </div>
</section>

<!-- Properties -->
<section class="properties-section" style="padding-top:40px;">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:10px;">
            <p style="color:#888;font-size:14px;">
                <?= $result['total'] ?> bien<?= $result['total'] !== 1 ? 's' : '' ?> trouvé<?= $result['total'] !== 1 ? 's' : '' ?>
            </p>
            <?php if ($searchVal || $typeVal || $maxPrice): ?>
            <a href="nos-biens.php" style="color:var(--gold);font-size:13px;"><i class="fas fa-times"></i> Réinitialiser les filtres</a>
            <?php endif; ?>
        </div>

        <div class="properties-grid">
            <?php foreach ($result['properties'] as $p): ?>
            <?php
            $imgUrl = $p['main_image'] ? '/uploads/'.$p['main_image'] : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070';
            $details = [];
            if ($p['bedrooms'])  $details[] = $p['bedrooms'].' ch.';
            if ($p['bathrooms']) $details[] = $p['bathrooms'].' SdB';
            if ($p['surface'])   $details[] = number_format((float)$p['surface'],0).' m²';
            ?>
            <div class="property-card">
                <a href="bien.php?id=<?= (int)$p['id'] ?>">
                    <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES|ENT_HTML5) ?>" alt="<?= htmlspecialchars($p['title'], ENT_QUOTES|ENT_HTML5) ?>">
                    <div class="property-info">
                        <h3><?= htmlspecialchars($p['title'], ENT_QUOTES|ENT_HTML5) ?></h3>
                        <p><?= htmlspecialchars(implode(', ',$details), ENT_QUOTES|ENT_HTML5) ?></p>
                        <div class="property-meta">
                            <span><?= htmlspecialchars($p['location'], ENT_QUOTES|ENT_HTML5) ?></span>
                            <span class="price"><?= number_format((float)$p['price'],0,',',' ') ?> €</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>

            <?php if (empty($result['properties'])): ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;">
                <i class="fas fa-home" style="font-size:48px;color:#ccc;margin-bottom:16px;display:block;"></i>
                <p style="color:#888;font-size:16px;">Aucun bien ne correspond à vos critères.</p>
                <a href="nos-biens.php" style="color:var(--gold);margin-top:12px;display:inline-block;">Voir tous les biens</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($result['last_page'] > 1): ?>
        <?php
        $baseParams = http_build_query(array_filter(['type'=>$typeVal,'search'=>$searchVal,'max_price'=>$maxPrice]));
        $sep = $baseParams ? '&' : '';
        ?>
        <div class="pagination-pub">
            <?php if ($page > 1): ?>
            <a href="nos-biens.php?<?= $baseParams ?><?= $sep ?>page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($i=1; $i<=$result['last_page']; $i++): ?>
            <a href="nos-biens.php?<?= $baseParams ?><?= $sep ?>page=<?= $i ?>" class="<?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $result['last_page']): ?>
            <a href="nos-biens.php?<?= $baseParams ?><?= $sep ?>page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Intéressé par un bien ?</h2>
        <p>Contactez notre équipe pour un rendez-vous personnel et une visite sur site.</p>
        <a href="contact.php" class="cta-btn">Nous contacter</a>
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-links">
            <a href="#">Mentions légales</a>
            <a href="contact.php">Contact</a>
            <a href="admin/login.php" style="color:rgba(255,255,255,0.3);font-size:12px;">Administration</a>
        </div>
        <p class="copyright">© 2025 H&M Immobilier. Tous droits réservés.</p>
    </div>
</footer>
</body>
</html>
