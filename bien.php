<?php
/**
 * H&M Immobilier — Page détail d'un Bien
 */

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/PropertyModel.php';

$model      = new PropertyModel();
$propertyId = sanitize_int($_GET['id'] ?? null);

if ($propertyId === null) {
    header('Location: /nos-biens.php');
    exit;
}

$property = $model->findById($propertyId);

// Vérifie que le bien existe et est actif
if ($property === null || $property['status'] !== 'active') {
    header('Location: /nos-biens.php');
    exit;
}

$images = $model->getImages($propertyId);

$imgUrl = $property['main_image']
    ? '/uploads/' . $property['main_image']
    : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title'], ENT_QUOTES | ENT_HTML5) ?> — H&M Immobilier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .bien-hero {
            height: 50vh;
            min-height: 380px;
            background: url('<?= htmlspecialchars($imgUrl, ENT_QUOTES | ENT_HTML5) ?>') no-repeat center/cover;
            position: relative;
            display: flex;
            align-items: flex-end;
        }
        .bien-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(transparent 30%, rgba(16,87,73,0.75) 100%);
        }
        .bien-hero .container {
            position: relative;
            z-index: 1;
            width: 100%;
            padding-bottom: 40px;
        }
        .bien-hero h1 {
            font-family: 'Playfair Display', serif;
            color: white;
            font-size: clamp(28px, 4vw, 42px);
            margin-bottom: 8px;
        }
        .bien-hero .bien-location {
            color: rgba(255,255,255,0.8);
            font-size: 16px;
        }
        .bien-hero .bien-price {
            color: var(--gold);
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 700;
            margin-top: 10px;
        }

        .bien-content {
            padding: 60px 0;
            background: white;
        }
        .bien-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 50px;
            align-items: start;
        }
        .bien-details h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 16px;
        }
        .bien-details p {
            color: #666;
            line-height: 1.8;
            font-size: 15px;
        }

        .bien-specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 32px;
        }
        .spec-item {
            background: var(--light);
            border-radius: 10px;
            padding: 18px;
            text-align: center;
        }
        .spec-item i {
            color: var(--gold);
            font-size: 22px;
            margin-bottom: 8px;
        }
        .spec-item .spec-value {
            font-weight: 700;
            color: var(--dark);
            font-size: 18px;
        }
        .spec-item .spec-label {
            color: #888;
            font-size: 12px;
            margin-top: 3px;
        }

        .bien-sidebar {
            background: var(--light);
            border-radius: 14px;
            padding: 30px;
            position: sticky;
            top: 90px;
        }
        .bien-sidebar .sidebar-price {
            font-size: 30px;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 20px;
        }
        .bien-sidebar .sidebar-price span {
            font-size: 14px;
            color: #888;
            font-weight: 400;
        }
        .bien-sidebar .sidebar-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e5e5;
            font-size: 14px;
        }
        .bien-sidebar .sidebar-item:last-of-type { border-bottom: none; }
        .bien-sidebar .sidebar-item .label { color: #888; }
        .bien-sidebar .sidebar-item .value { font-weight: 600; color: var(--dark); }

        .bien-sidebar .btn-contact {
            display: block;
            text-align: center;
            background: var(--primary);
            color: white;
            padding: 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 24px;
            transition: var(--transition);
        }
        .bien-sidebar .btn-contact:hover { background: var(--gold); color: var(--primary); }

        /* Galerie */
        .bien-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 32px;
        }
        .bien-gallery img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }
        .bien-gallery img:hover { transform: scale(1.03); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        /* Commodités */
        .commodites {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .commodite-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16,87,73,0.08);
            color: var(--primary);
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .commodite-tag i { color: var(--gold); }

        @media (max-width: 768px) {
            .bien-grid { grid-template-columns: 1fr; }
            .bien-sidebar { position: static; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="container">
            <div class="logo">H&M <span>Immobilier</span></div>
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="nos-biens.php" style="color: var(--gold);">Nos Biens</a></li>
                <li><a href="services.html">Services</a></li>
                <li><a href="a-propos.html">À Propos</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="mobile-menu-bar">
            <div class="mobile-menu-item"><a href="index.html"><span>Accueil</span></a></div>
            <div class="mobile-menu-item"><a href="nos-biens.php" style="color:var(--gold);"><span>Nos Biens</span></a></div>
            <div class="mobile-menu-item"><a href="services.html"><span>Services</span></a></div>
            <div class="mobile-menu-item"><a href="a-propos.html"><span>À Propos</span></a></div>
            <div class="mobile-menu-item"><a href="contact.html"><span>Contact</span></a></div>
        </div>
    </div>

    <!-- Hero image du bien -->
    <section class="bien-hero">
        <div class="container">
            <h1><?= htmlspecialchars($property['title'], ENT_QUOTES | ENT_HTML5) ?></h1>
            <p class="bien-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['location'], ENT_QUOTES | ENT_HTML5) ?></p>
            <p class="bien-price"><?= number_format((float)$property['price'], 0, ',', ' ') ?> €</p>
        </div>
    </section>

    <!-- Contenu détail -->
    <section class="bien-content">
        <div class="container">
            <div class="bien-grid">
                <!-- Partie principale -->
                <div class="bien-details">
                    <!-- Description -->
                    <?php if ($property['description']): ?>
                    <h2>À propos de ce bien</h2>
                    <p><?= htmlspecialchars($property['description'], ENT_QUOTES | ENT_HTML5) ?></p>
                    <?php endif; ?>

                    <!-- Specifications -->
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
                            <div class="spec-value"><?= number_format((float)$property['surface'], 0) ?></div>
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
                    if ($property['has_pool'])   $commodites[] = ['fas fa-swimming-pool', 'Piscine'];
                    if ($property['has_garage']) $commodites[] = ['fas fa-car',            'Garage'];
                    if ($property['has_garden']) $commodites[] = ['fas fa-leaf',           'Jardin'];
                    ?>
                    <?php if (!empty($commodites)): ?>
                    <h2 style="margin-top: 36px;">Commodités</h2>
                    <div class="commodites">
                        <?php foreach ($commodites as [$icon, $label]): ?>
                        <span class="commodite-tag">
                            <i class="<?= $icon ?>"></i> <?= $label ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Galerie supplémentaire -->
                    <?php if (!empty($images)): ?>
                    <h2 style="margin-top: 36px;">Galerie</h2>
                    <div class="bien-gallery">
                        <?php foreach ($images as $img): ?>
                        <img src="/uploads/<?= htmlspecialchars($img['file_path'], ENT_QUOTES | ENT_HTML5) ?>"
                             alt="Image du bien">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <aside class="bien-sidebar">
                    <div class="sidebar-price">
                        <?= number_format((float)$property['price'], 0, ',', ' ') ?> € <span>/ prix demandé</span>
                    </div>

                    <div class="sidebar-item">
                        <span class="label">Type</span>
                        <span class="value"><?= ucfirst($property['property_type']) ?></span>
                    </div>
                    <div class="sidebar-item">
                        <span class="label">Lieu</span>
                        <span class="value"><?= htmlspecialchars($property['location'], ENT_QUOTES | ENT_HTML5) ?></span>
                    </div>
                    <?php if ($property['surface']): ?>
                    <div class="sidebar-item">
                        <span class="label">Surface</span>
                        <span class="value"><?= number_format((float)$property['surface'], 0) ?> m²</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($property['bedrooms']): ?>
                    <div class="sidebar-item">
                        <span class="label">Chambres</span>
                        <span class="value"><?= (int)$property['bedrooms'] ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="sidebar-item">
                        <span class="label">Gestionnaire</span>
                        <span class="value"><?= htmlspecialchars($property['manager_name'], ENT_QUOTES | ENT_HTML5) ?></span>
                    </div>

                    <a href="contact.html" class="btn-contact">
                        <i class="fas fa-envelope" style="margin-right: 8px;"></i>Contacter un Agent
                    </a>
                </aside>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">Mentions légales</a>
                <a href="contact.html">Contact</a>
                <a href="/admin/login.php" style="color: rgba(255,255,255,0.3); font-size: 12px;">Administration</a>
            </div>
            <p class="copyright">© 2025 H&M Immobilier. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
