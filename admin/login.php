<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === false || empty($password)) {
        $error = 'Identifiants invalides.';
        audit_log(null, 'login_failed', null, 'Champs manquants ou email invalide');
    } else {
        $rlKey = 'login_' . get_client_ip();
        if (rate_limit($rlKey, 5, 300)) {
            $error = 'Trop de tentatives. Réessayez dans quelques minutes.';
            audit_log(null, 'login_rate_limited', null, "IP: " . get_client_ip());
        } else {
            $userModel = new UserModel();
            $user      = $userModel->authenticate($email, $password);
            if ($user === null) {
                $error = 'Email ou mot de passe incorrect.';
                audit_log(null, 'login_failed', "user_email:$email", 'Mot de passe incorrect');
            } else {
                rate_limit_reset($rlKey);
                session_login($user);
                audit_log((int)$user['id'], 'login', "user:{$user['id']}", 'Connexion réussie');
                header('Location: /admin/dashboard.php');
                exit;
            }
        }
    }
}

if (isset($_SESSION['user_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H&M Immobilier | Connexion Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="page-login">
    <div class="login-container">
        <div class="login-brand">
            <div class="logo-icon"><i class="fas fa-building"></i></div>
            <h1>H&M <span>Immobilier</span></h1>
            <p>Espace Administration</p>
        </div>
        <div class="login-card">
            <?php if ($error): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= htmlspecialchars($error, ENT_QUOTES | ENT_HTML5) ?></p>
            </div>
            <?php endif; ?>
            <form method="POST" action="login.php" novalidate class="login-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
            </form>
        </div>
        <div class="back-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Retour au site</a></div>
    </div>
</body>
</html>
