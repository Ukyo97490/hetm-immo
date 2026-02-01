<?php
/**
 * H&M Immobilier — Homepage
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H&M Immobilier | Trouvez la propriété de vos rêves</title>
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
            <li><a href="index.php" class="active">Accueil</a></li>
            <li><a href="nos-biens.php">Nos Biens</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="a-propos.php">À Propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu">
    <div class="mobile-menu-bar">
        <div class="mobile-menu-item active"><a href="index.php"><i class="fas fa-home"></i><span>Accueil</span></a></div>
        <div class="mobile-menu-item"><a href="nos-biens.php"><i class="fas fa-building"></i><span>Nos Biens</span></a></div>
        <div class="mobile-menu-item"><a href="services.php"><i class="fas fa-handshake"></i><span>Services</span></a></div>
        <div class="mobile-menu-item"><a href="a-propos.php"><i class="fas fa-users"></i><span>À Propos</span></a></div>
        <div class="mobile-menu-item"><a href="contact.php"><i class="fas fa-envelope"></i><span>Contact</span></a></div>
    </div>
</div>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Trouvez la propriété de vos rêves</h1>
            <p>Découvrez nos biens immobiliers d'exception, sélectionnés pour leur prestige et leur emplacement idéal.</p>
            <div class="search-form">
                <form method="GET" action="nos-biens.php">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Localisation">
                        </div>
                        <div class="form-group">
                            <select name="type">
                                <option value="">Type de bien</option>
                                <option value="maison">Maison</option>
                                <option value="appartement">Appartement</option>
                                <option value="terrain">Terrain</option>
                                <option value="villa">Villa</option>
                                <option value="chalet">Chalet</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" name="max_price" placeholder="Budget max €">
                        </div>
                    </div>
                    <button type="submit" class="btn-pub">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="properties-section">
    <div class="container">
        <h2 class="section-title">Nos Biens en Vedette</h2>
        <div class="properties-grid" id="featuredProperties"></div>
        <div style="text-align:center; margin-top:40px;">
            <a href="nos-biens.php" class="btn-pub btn-pub-outline">Voir tous les biens</a>
        </div>
    </div>
</section>

<!-- Services -->
<section class="services-section">
    <div class="container">
        <h2 class="section-title">Pourquoi Nous Choisir ?</h2>
        <div class="services-grid">
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070" alt="Biens d'Exception" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-home"></i></div>
                    <h3>Biens d'Exception</h3>
                    <p>Nous sélectionnons des propriétés uniques pour leur prestige, leur emplacement et leur potentiel.</p>
                    <a href="services.php" class="service-btn">En savoir plus</a>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?q=80&w=2070" alt="Service Personnalisé" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-handshake"></i></div>
                    <h3>Service Personnalisé</h3>
                    <p>Un accompagnement sur mesure pour chaque client, du premier contact à la signature.</p>
                    <a href="services.php" class="service-btn">En savoir plus</a>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=2070" alt="Expertise & Confiance" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Expertise & Confiance</h3>
                    <p>10 ans d'expérience dans l'immobilier de luxe et des centaines de clients satisfaits.</p>
                    <a href="services.php" class="service-btn">En savoir plus</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Prêt à trouver votre prochain bien ?</h2>
        <p>Notre équipe est à votre disposition pour vous guider dans votre projet immobilier, que ce soit pour acheter, vendre ou investir.</p>
        <a href="contact.php" class="cta-btn">Nous contacter</a>
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-links">
            <a href="#">Mentions légales</a>
            <a href="contact.php">Contact</a>
            <a href="admin/login.php" style="color:rgba(255,255,255,0.3); font-size:12px;">Administration</a>
        </div>
        <p class="copyright">© 2025 H&M Immobilier. Tous droits réservés.</p>
    </div>
</footer>

<script>
(async function() {
    try {
        const res  = await fetch('/api/featured-properties.php');
        const data = await res.json();
        const grid = document.getElementById('featuredProperties');
        if (!grid) return;
        if (data.length === 0) {
            grid.innerHTML = '<p style="text-align:center;color:#888;padding:40px 0;grid-column:1/-1;">Aucun bien disponible pour le moment.</p>';
            return;
        }
        data.forEach(p => {
            const imgUrl = p.main_image ? '/uploads/' + p.main_image : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070';
            grid.innerHTML += `
                <div class="property-card">
                    <a href="bien.php?id=${parseInt(p.id)}">
                        <img src="${imgUrl}" alt="${p.title.replace(/"/g,'&quot;')}">
                        <div class="property-info">
                            <h3>${p.title}</h3>
                            <p>${p.details}</p>
                            <div class="property-meta">
                                <span>${p.location}</span>
                                <span class="price">${p.price_formatted} €</span>
                            </div>
                        </div>
                    </a>
                </div>`;
        });
    } catch(e) { console.error('Erreur chargement biens:', e); }
})();
</script>
</body>
</html>
