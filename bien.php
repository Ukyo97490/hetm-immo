<?php
/**
 * H&M Immobilier — Détail d'un Bien
 */
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/PropertyModel.php';

$model      = new PropertyModel();
$propertyId = sanitize_int($_GET['id'] ?? null);

if ($propertyId === null) { header('Location: nos-biens.php'); exit; }

$property = $model->findById($propertyId);
if ($property === null || $property['status'] !== 'active') { header('Location: nos-biens.php'); exit; }

$images = $model->getImages($propertyId);
$imgUrl = $property['main_image'] ? '/uploads/'.$property['main_image'] : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title'], ENT_QUOTES|ENT_HTML5) ?> — H&M Immobilier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .bien-hero { background-image: url('<?= htmlspecialchars($imgUrl, ENT_QUOTES|ENT_HTML5) ?>'); }
    </style>
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

<!-- Hero -->
<section class="bien-hero">
    <div class="container">
        <h1><?= htmlspecialchars($property['title'], ENT_QUOTES|ENT_HTML5) ?></h1>
        <p class="bien-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['location'], ENT_QUOTES|ENT_HTML5) ?></p>
        <p class="bien-price"><?= number_format((float)$property['price'],0,',',' ') ?> €</p>
    </div>
</section>

<!-- Content -->
<section class="bien-content">
    <div class="container">
        <div class="bien-grid">
            <!-- Main -->
            <div class="bien-details">
                <?php if ($property['description']): ?>
                <h2>À propos de ce bien</h2>
                <p><?= htmlspecialchars($property['description'], ENT_QUOTES|ENT_HTML5) ?></p>
                <?php endif; ?>

                <!-- Specs -->
                <div class="bien-specs">
                    <?php if ($property['bedrooms']): ?>
                    <div class="spec-item">
                        <i class="fas fa-bed"></i>
                        <div class="spec-value"><?= (int)$property['bedrooms'] ?></div>
                        <div class="spec-label">Chambres</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($property['bathrooms']): ?>
                    <div class="spec-item">
                        <i class="fas fa-bath"></i>
                        <div class="spec-value"><?= (int)$property['bathrooms'] ?></div>
                        <div class="spec-label">Salles de Bain</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($property['surface']): ?>
                    <div class="spec-item">
                        <i class="fas fa-vector-square"></i>
                        <div class="spec-value"><?= number_format((float)$property['surface'],0) ?></div>
                        <div class="spec-label">Surface (m²)</div>
                    </div>
                    <?php endif; ?>
                    <div class="spec-item">
                        <i class="fas fa-building"></i>
                        <div class="spec-value"><?= ucfirst($property['property_type']) ?></div>
                        <div class="spec-label">Type</div>
                    </div>
                </div>

                <!-- Commodités -->
                <?php
                $commodites = [];
                if ($property['has_pool'])   $commodites[] = ['fas fa-swimming-pool','Piscine'];
                if ($property['has_garage']) $commodites[] = ['fas fa-car','Garage'];
                if ($property['has_garden']) $commodites[] = ['fas fa-leaf','Jardin'];
                ?>
                <?php if (!empty($commodites)): ?>
                <h2 style="margin-top:36px;">Commodités</h2>
                <div class="commodites">
                    <?php foreach ($commodites as [$icon,$label]): ?>
                    <span class="commodite-tag"><i class="<?= $icon ?>"></i> <?= $label ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Gallery -->
                <?php if (!empty($images)): ?>
                <h2 style="margin-top:36px;">Galerie</h2>
                <div class="bien-gallery">
                    <?php foreach ($images as $img): ?>
                    <img src="uploads/<?= htmlspecialchars($img['file_path'], ENT_QUOTES|ENT_HTML5) ?>" alt="Image du bien">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="bien-sidebar">
                <div class="sidebar-price">
                    <?= number_format((float)$property['price'],0,',',' ') ?> € <span>/ prix demandé</span>
                </div>
                <div class="sidebar-item"><span class="label">Type</span><span class="value"><?= ucfirst($property['property_type']) ?></span></div>
                <div class="sidebar-item"><span class="label">Lieu</span><span class="value"><?= htmlspecialchars($property['location'], ENT_QUOTES|ENT_HTML5) ?></span></div>
                <?php if ($property['surface']): ?>
                <div class="sidebar-item"><span class="label">Surface</span><span class="value"><?= number_format((float)$property['surface'],0) ?> m²</span></div>
                <?php endif; ?>
                <?php if ($property['bedrooms']): ?>
                <div class="sidebar-item"><span class="label">Chambres</span><span class="value"><?= (int)$property['bedrooms'] ?></span></div>
                <?php endif; ?>
                <div class="sidebar-item"><span class="label">Gestionnaire</span><span class="value"><?= htmlspecialchars($property['manager_name'], ENT_QUOTES|ENT_HTML5) ?></span></div>
                <a href="contact.php" class="btn-contact"><i class="fas fa-envelope" style="margin-right:8px;"></i>Contacter un Agent</a>
            </aside>
        </div>
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
