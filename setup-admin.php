<?php
/**
 * H&M Immobilier — Setup du compte admin par défaut
 * Exécuter UNE SEULE FOIS après avoir lancé database.sql
 * Le script se supprime automatiquement après création.
 */
require_once __DIR__ . '/config.php';

$db = getDB();

$alreadyExists = false;
$created       = false;

// Vérifie si un admin existe déjà
$check = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'admin'");
if ((int)$check->fetch()['c'] > 0) {
    $alreadyExists = true;
} else {
    $email    = 'admin@hm-immobilier.fr';
    $password = 'admin123';
    $hash     = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare(
        "INSERT INTO users (email, password_hash, first_name, last_name, role) VALUES (?, ?, 'Admin', 'Principal', 'admin')"
    );
    $stmt->execute([$email, $hash]);
    $created = true;

    // Auto-suppression du script
    unlink(__FILE__);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin — H&M Immobilier</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f0f4f3; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card { background:white; border-radius:14px; box-shadow:0 8px 40px rgba(0,0,0,0.08); max-width:460px; width:100%; padding:48px 40px; text-align:center; }
        .icon { width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; }
        .icon.green { background:#105749; }
        .icon.info  { background:#D4AF37; }
        .icon svg { width:36px; height:36px; fill:white; }
        h1 { color:#105749; font-size:22px; margin-bottom:12px; }
        .card > p { color:#666; font-size:14px; line-height:1.6; margin-bottom:28px; }
        .creds { background:#f0f7f5; border:1px solid #d4edda; border-radius:8px; padding:18px 20px; margin-bottom:28px; text-align:left; }
        .creds .label { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:3px; font-weight:600; }
        .creds .value { font-size:15px; color:#105749; font-weight:600; font-family:monospace; margin-bottom:12px; }
        .creds .value:last-child { margin-bottom:0; }
        .btn { display:inline-block; background:#D4AF37; color:#105749; text-decoration:none; padding:12px 32px; border-radius:8px; font-weight:700; font-size:15px; transition:background 0.2s; }
        .btn:hover { background:#105749; color:#D4AF37; }
        .note { font-size:12px; color:#999; margin-top:20px; }
    </style>
</head>
<body>
<div class="card">
    <?php if ($alreadyExists): ?>
        <div class="icon info">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <h1>Un compte admin existe déjà</h1>
        <p>Rien à faire ici. Vous pouvez vous connecter directement.</p>
        <a href="admin/login.php" class="btn">Se connecter →</a>
    <?php else: ?>
        <div class="icon green">
            <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
        </div>
        <h1>Compte Admin Créé</h1>
        <p>Le compte administrateur par défaut a été créé avec succès. Ce script a été supprimé automatiquement.</p>
        <div class="creds">
            <div class="label">Email</div>
            <div class="value">admin@hm-immobilier.fr</div>
            <div class="label">Mot de passe</div>
            <div class="value">admin123</div>
        </div>
        <a href="admin/login.php" class="btn">Se connecter →</a>
        <p class="note">Changez votre mot de passe immédiatement après connexion.</p>
    <?php endif; ?>
</div>
</body>
</html>
