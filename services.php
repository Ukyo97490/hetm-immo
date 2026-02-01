<?php /** H&M Immobilier — Services */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H&M Immobilier | Nos Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>

<header class="site-header">
    <nav>
        <div class="logo">H&M <span>Immobilier</span></div>
        <ul class="nav-links">
            <li><a href="/index.php">Accueil</a></li>
            <li><a href="/nos-biens.php">Nos Biens</a></li>
            <li><a href="/services.php" class="active">Services</a></li>
            <li><a href="/a-propos.php">À Propos</a></li>
            <li><a href="/contact.php">Contact</a></li>
        </ul>
    </nav>
</header>
<div class="mobile-menu">
    <div class="mobile-menu-bar">
        <div class="mobile-menu-item"><a href="/index.php"><i class="fas fa-home"></i><span>Accueil</span></a></div>
        <div class="mobile-menu-item"><a href="/nos-biens.php"><i class="fas fa-building"></i><span>Nos Biens</span></a></div>
        <div class="mobile-menu-item active"><a href="/services.php"><i class="fas fa-handshake"></i><span>Services</span></a></div>
        <div class="mobile-menu-item"><a href="/a-propos.php"><i class="fas fa-users"></i><span>À Propos</span></a></div>
        <div class="mobile-menu-item"><a href="/contact.php"><i class="fas fa-envelope"></i><span>Contact</span></a></div>
    </div>
</div>

<section class="page-title">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
        <h1>Nos Services</h1>
        <p>Une offre complète pour accompagner chaque étape de votre projet immobilier.</p>
    </div>
</section>

<!-- Services détails -->
<section class="services-section">
    <div class="container">
        <div class="services-grid">
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070" alt="Achat Immobilier" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-key"></i></div>
                    <h3>Achat Immobilier</h3>
                    <p>Nous vous accompagnons dans la recherche du bien idéal selon vos critères, votre budget et vos besoins. De la première visite à la signature, notre équipe d'experts est à vos côtés.</p>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?q=80&w=2070" alt="Vente Immobilier" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-tags"></i></div>
                    <h3>Vente Immobilier</h3>
                    <p>Valorisez votre bien avec notre expertise en estimation, communication et négociation. Nous gérons toutes les étapes pour vous garantir une vente rapide au meilleur prix.</p>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=2070" alt="Investissement" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Investissement</h3>
                    <p>Construisez votre patrimoine avec nos conseils en investissement immobilier. Nous analysons les marchés et vous proposons des opportunités à haut potentiel de rendement.</p>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=2070" alt="Gestion Locative" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-cog"></i></div>
                    <h3>Gestion Locative</h3>
                    <p>Confiez la gestion de votre bien à notre équipe professionnelle. Recherche de locataires, suivi administratif, maintenance — nous prennons tout en charge.</p>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?q=80&w=2070" alt="Conseil Juridique" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-balance-scale"></i></div>
                    <h3>Conseil Juridique</h3>
                    <p>Notre partenariat avec des cabinets juridiques spécialisés vous garantit une protection optimale à chaque étape de votre projet immobilier.</p>
                </div>
            </div>
            <div class="service-card">
                <img src="https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?q=80&w=2070" alt="Estimation" class="service-image">
                <div class="service-content">
                    <div class="service-icon"><i class="fas fa-calculator"></i></div>
                    <h3>Estimation Gratuite</h3>
                    <p>Obtenez une estimation précise et fiable de votre bien immobilier. Notre méthode combine l'analyse du marché local et l'expertise terrain de nos agents.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Prêt à démarrer votre projet ?</h2>
        <p>Contactez-nous dès aujourd'hui pour une consultation gratuite avec l'un de nos experts.</p>
        <a href="/contact.php" class="cta-btn">Nous contacter</a>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="footer-links">
            <a href="#">Mentions légales</a>
            <a href="/contact.php">Contact</a>
            <a href="/admin/login.php" style="color:rgba(255,255,255,0.3);font-size:12px;">Administration</a>
        </div>
        <p class="copyright">© 2025 H&M Immobilier. Tous droits réservés.</p>
    </div>
</footer>
</body>
</html>
