<?php /** H&M Immobilier — Contact */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H&M Immobilier | Contact</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .contact-section { padding:80px 0; background:white; }
        .contact-grid { display:grid; grid-template-columns:1fr 1fr; gap:60px; max-width:1200px; margin:0 auto; padding:0 20px; align-items:start; }
        .contact-info h2 { font-family:'Playfair Display',serif; color:var(--primary); font-size:32px; margin-bottom:16px; }
        .contact-info > p { color:#555; line-height:1.8; font-size:15px; margin-bottom:36px; }
        .contact-item { display:flex; gap:16px; margin-bottom:28px; align-items:flex-start; }
        .contact-item .ci-icon { width:48px; height:48px; background:var(--gold); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:18px; flex-shrink:0; }
        .contact-item .ci-text h4 { color:var(--primary); font-size:15px; margin-bottom:4px; }
        .contact-item .ci-text p { color:#888; font-size:14px; line-height:1.5; }
        .contact-form-wrap { background:var(--light); border-radius:14px; padding:40px; }
        .contact-form-wrap h3 { font-family:'Playfair Display',serif; color:var(--primary); font-size:24px; margin-bottom:8px; }
        .contact-form-wrap > p { color:#888; font-size:14px; margin-bottom:28px; }
        .cf-row { display:flex; gap:16px; }
        .cf-row .cf-group { flex:1; }
        .cf-group { margin-bottom:20px; }
        .cf-group label { display:block; font-size:13px; font-weight:600; color:var(--gray-600); margin-bottom:6px; }
        .cf-group input, .cf-group select, .cf-group textarea {
            width:100%; padding:12px 14px; border:1px solid var(--gray-300); border-radius:var(--radius-sm);
            font-size:14px; color:var(--dark); background:white; outline:none; transition:border-color 0.3s;
        }
        .cf-group input:focus, .cf-group select:focus, .cf-group textarea:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(16,87,73,0.12); }
        .cf-group textarea { resize:vertical; min-height:120px; }
        .cf-submit {
            background:var(--gold); color:var(--primary); border:none; padding:13px 32px;
            border-radius:var(--radius-sm); font-size:15px; font-weight:600; cursor:pointer; transition:var(--transition);
            display:flex; align-items:center; gap:8px;
        }
        .cf-submit:hover { background:var(--primary); color:var(--gold); }
        .success-msg { background:var(--success-bg); border:1px solid rgba(39,174,96,0.25); border-radius:var(--radius); padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; gap:10px; }
        .success-msg i { color:var(--success); font-size:18px; }
        .success-msg span { color:#1e8449; font-size:14px; }
        @media(max-width:768px){ .contact-grid{grid-template-columns:1fr;} .cf-row{flex-direction:column;gap:0;} .contact-form-wrap{padding:28px;} }
    </style>
</head>
<body>
<?php
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cf_name'])) {
    $sent = true;
}
?>

<header class="site-header">
    <nav>
        <div class="logo">H&M <span>Immobilier</span></div>
        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="nos-biens.php">Nos Biens</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="a-propos.php">À Propos</a></li>
            <li><a href="contact.php" class="active">Contact</a></li>
        </ul>
    </nav>
</header>
<div class="mobile-menu">
    <div class="mobile-menu-bar">
        <div class="mobile-menu-item"><a href="index.php"><i class="fas fa-home"></i><span>Accueil</span></a></div>
        <div class="mobile-menu-item"><a href="nos-biens.php"><i class="fas fa-building"></i><span>Nos Biens</span></a></div>
        <div class="mobile-menu-item"><a href="services.php"><i class="fas fa-handshake"></i><span>Services</span></a></div>
        <div class="mobile-menu-item"><a href="a-propos.php"><i class="fas fa-users"></i><span>À Propos</span></a></div>
        <div class="mobile-menu-item active"><a href="contact.php"><i class="fas fa-envelope"></i><span>Contact</span></a></div>
    </div>
</div>

<section class="page-title">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
        <h1>Contact</h1>
        <p>Nous sommes à votre disposition pour tout projet immobilier.</p>
    </div>
</section>

<section class="contact-section">
    <div class="contact-grid">
        <div class="contact-info">
            <h2>Parlons de votre projet</h2>
            <p>Notre équipe professionnelle est disponible du lundi au vendredi pour vous conseiller et vous accompagner. N'hésitez pas à nous contacter par les moyens ci-dessous.</p>
            <div class="contact-item">
                <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="ci-text"><h4>Notre Bureau</h4><p>47, Rue de la Paix<br>75002 Paris</p></div>
            </div>
            <div class="contact-item">
                <div class="ci-icon"><i class="fas fa-phone"></i></div>
                <div class="ci-text"><h4>Téléphone</h4><p>+33 1 23 45 67 89</p></div>
            </div>
            <div class="contact-item">
                <div class="ci-icon"><i class="fas fa-envelope"></i></div>
                <div class="ci-text"><h4>Email</h4><p>contact@hm-immobilier.fr</p></div>
            </div>
            <div class="contact-item">
                <div class="ci-icon"><i class="fas fa-clock"></i></div>
                <div class="ci-text"><h4>Horaires</h4><p>Lun – Ven : 9h00 – 18h00<br>Sam : 10h00 – 16h00</p></div>
            </div>
        </div>
        <div class="contact-form-wrap">
            <?php if ($sent): ?>
            <div class="success-msg"><i class="fas fa-check-circle"></i><span>Merci ! Votre message a bien été reçu. Nous vous contacterons prochainement.</span></div>
            <?php endif; ?>
            <h3>Envoyer un message</h3>
            <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les 24h.</p>
            <form method="POST" action="contact.php" novalidate>
                <div class="cf-row">
                    <div class="cf-group"><label>Prénom</label><input type="text" name="cf_name" placeholder="Jean" required></div>
                    <div class="cf-group"><label>Nom</label><input type="text" name="cf_lastname" placeholder="Dupont" required></div>
                </div>
                <div class="cf-group"><label>Email</label><input type="email" name="cf_email" placeholder="jean@example.com" required></div>
                <div class="cf-group"><label>Téléphone</label><input type="tel" name="cf_phone" placeholder="+33 6 00 00 00 00"></div>
                <div class="cf-group">
                    <label>Sujet</label>
                    <select name="cf_subject">
                        <option value="">— Choisir —</option>
                        <option value="achat">Achat d'un bien</option>
                        <option value="vente">Vente d'un bien</option>
                        <option value="investissement">Investissement</option>
                        <option value="estimation">Estimation gratuite</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="cf-group"><label>Message</label><textarea name="cf_message" placeholder="Décrivez votre projet..." required></textarea></div>
                <button type="submit" class="cf-submit"><i class="fas fa-paper-plane"></i> Envoyer le message</button>
            </form>
        </div>
    </div>
</section>

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
