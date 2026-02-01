<?php /** H&M Immobilier — À Propos */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H&M Immobilier | À Propos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
    <style>
        .about-hero {
            height: 45vh; min-height:320px;
            background: linear-gradient(rgba(16,87,73,0.72),rgba(16,87,73,0.72)),
                        url('https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=2070') no-repeat center/cover;
            display:flex; align-items:center; text-align:center; color:white; padding-top:80px;
        }
        .about-hero h1 { font-family:'Playfair Display',serif; font-size:clamp(32px,5vw,48px); margin-bottom:16px; }
        .about-hero p { font-size:clamp(16px,2.5vw,18px); max-width:650px; margin:0 auto; opacity:.85; }

        .about-content { padding:80px 0; background:white; }
        .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center; max-width:1200px; margin:0 auto; padding:0 20px; }
        .about-text h2 { font-family:'Playfair Display',serif; color:var(--primary); font-size:32px; margin-bottom:20px; }
        .about-text p { color:#555; line-height:1.9; font-size:15px; margin-bottom:16px; }
        .about-img { border-radius:14px; overflow:hidden; box-shadow:var(--shadow-md); height:420px; }
        .about-img img { width:100%; height:100%; object-fit:cover; }

        .stats-row { display:flex; gap:40px; margin-top:40px; flex-wrap:wrap; }
        .stat-item { text-align:center; }
        .stat-item .num { font-family:'Playfair Display',serif; font-size:40px; font-weight:700; color:var(--gold); }
        .stat-item .lbl { font-size:13px; color:#888; margin-top:4px; }

        .team-section { padding:80px 0; background:var(--light); }
        .team-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:30px; max-width:1200px; margin:0 auto; padding:0 20px; }
        .team-card { background:white; border-radius:12px; overflow:hidden; box-shadow:var(--shadow); text-align:center; transition:var(--transition); }
        .team-card:hover { transform:translateY(-4px); }
        .team-card .avatar { width:100%; height:220px; background:var(--primary); display:flex; align-items:center; justify-content:center; }
        .team-card .avatar i { font-size:80px; color:var(--gold); opacity:.6; }
        .team-card .info { padding:24px 20px; }
        .team-card .info h3 { font-family:'Playfair Display',serif; color:var(--primary); margin-bottom:6px; font-size:18px; }
        .team-card .info .role { color:var(--gold); font-size:13px; font-weight:600; }
        .team-card .info p { color:#888; font-size:13px; margin-top:8px; line-height:1.5; }

        @media(max-width:768px) {
            .about-grid { grid-template-columns:1fr; }
            .about-img { height:260px; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <nav>
        <div class="logo">H&M <span>Immobilier</span></div>
        <ul class="nav-links">
            <li><a href="/index.php">Accueil</a></li>
            <li><a href="/nos-biens.php">Nos Biens</a></li>
            <li><a href="/services.php">Services</a></li>
            <li><a href="/a-propos.php" class="active">À Propos</a></li>
            <li><a href="/contact.php">Contact</a></li>
        </ul>
    </nav>
</header>
<div class="mobile-menu">
    <div class="mobile-menu-bar">
        <div class="mobile-menu-item"><a href="/index.php"><i class="fas fa-home"></i><span>Accueil</span></a></div>
        <div class="mobile-menu-item"><a href="/nos-biens.php"><i class="fas fa-building"></i><span>Nos Biens</span></a></div>
        <div class="mobile-menu-item"><a href="/services.php"><i class="fas fa-handshake"></i><span>Services</span></a></div>
        <div class="mobile-menu-item active"><a href="/a-propos.php"><i class="fas fa-users"></i><span>À Propos</span></a></div>
        <div class="mobile-menu-item"><a href="/contact.php"><i class="fas fa-envelope"></i><span>Contact</span></a></div>
    </div>
</div>

<!-- Hero -->
<section class="about-hero">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
        <h1>À Propos de Nous</h1>
        <p>Une décennie de passion, d'expertise et d'engagement envers l'excellence immobilière.</p>
    </div>
</section>

<!-- About content -->
<section class="about-content">
    <div class="about-grid">
        <div class="about-text">
            <h2>Notre Histoire</h2>
            <p>Fondé en 2015, H&M Immobilier est né d'une vision claire : transformer l'expérience d'achat et de vente immobilière en France. Notre fondateur, issu d'une famille d'agents immobiliers, a décidé de créer une agence qui mettait l'excellence au premier plan.</p>
            <p>Aujourd'hui, nous sommes l'une des agences les plus reconnues de la région, avec un portefeuille de plus de 200 biens d'exception et une équipe de professionnels passionnés.</p>
            <p>Notre engagement est simple : vous offrir le meilleur service possible, avec une transparence et une écoute qui font la différence.</p>
            <div class="stats-row">
                <div class="stat-item"><div class="num">10+</div><div class="lbl">années d'expérience</div></div>
                <div class="stat-item"><div class="num">500+</div><div class="lbl">clients satisfaits</div></div>
                <div class="stat-item"><div class="num">200+</div><div class="lbl">biens vendus</div></div>
            </div>
        </div>
        <div class="about-img">
            <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2070" alt="Notre équipe">
        </div>
    </div>
</section>

<!-- Équipe -->
<section class="team-section">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
        <h2 class="section-title">Notre Équipe</h2>
        <div class="team-grid">
            <div class="team-card">
                <div class="avatar"><i class="fas fa-user-tie"></i></div>
                <div class="info">
                    <h3>Marc Delacroix</h3>
                    <div class="role">Fondateur & Directeur</div>
                    <p>20 ans d'expérience en immobilier de luxe. Visionnaire et leader, il guide l'agence vers l'excellence.</p>
                </div>
            </div>
            <div class="team-card">
                <div class="avatar"><i class="fas fa-user-tie"></i></div>
                <div class="info">
                    <h3>Sophie Laurent</h3>
                    <div class="role">Directrice Commerciale</div>
                    <p>Spécialiste en biens prestige, Sophie apporte une expertise incontestable dans la valorisation des propriétés.</p>
                </div>
            </div>
            <div class="team-card">
                <div class="avatar"><i class="fas fa-user-tie"></i></div>
                <div class="info">
                    <h3>Thomas Renard</h3>
                    <div class="role">Agent Immobilier Senior</div>
                    <p>Connaisseur du marché local, Thomas accompagne ses clients avec une approche personnalisée et rigoureuse.</p>
                </div>
            </div>
            <div class="team-card">
                <div class="avatar"><i class="fas fa-user-tie"></i></div>
                <div class="info">
                    <h3>Clara Moreau</h3>
                    <div class="role">Conseillère en Investissement</div>
                    <p>Diplômée en finance, Clara aiguille ses clients vers les meilleures opportunités d'investissement immobilier.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Faisons connaissance !</h2>
        <p>Visitez notre bureau ou contactez-nous pour discuter de votre projet immobilier.</p>
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
