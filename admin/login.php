<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();

$error = '';

// ── Traitement de la connexion ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === false || empty($password)) {
        $error = 'Identifiants invalides.';
        audit_log(null, 'login_failed', null, 'Champs manquants ou email invalide');
    } else {
        // Rate limiting : 5 tentatives en 5 minutes par IP
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
                // Connexion réussie
                rate_limit_reset($rlKey);
                session_login($user);
                audit_log((int)$user['id'], 'login', "user:{$user['id']}", 'Connexion réussie');
                header('Location: /admin/dashboard.php');
                exit;
            }
        }
    }
}

// Si déjà connecté, redirige vers le dashboard
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
    <style>
        :root {
            --primary: #105749;
            --primary-dark: #0b3d32;
            --gold: #D4AF37;
            --gold-light: #e8c85a;
            --light: #F8F8F8;
            --dark: #1A1A1A;
            --error: #c0392b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, #1a7a63 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Décoration d'arrière-plan */
        body::before {
            content: '';
            position: absolute;
            top: -200px; right: -200px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -150px; left: -150px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212,175,55,0.06) 0%, transparent 70%);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* Logo & branding */
        .login-brand {
            text-align: center;
            margin-bottom: 40px;
        }
        .login-brand .logo-icon {
            width: 70px; height: 70px;
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(212,175,55,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .login-brand .logo-icon i {
            font-size: 28px;
            color: var(--gold);
        }
        .login-brand h1 {
            font-family: 'Playfair Display', serif;
            color: white;
            font-size: 28px;
            font-weight: 700;
        }
        .login-brand h1 span { color: var(--gold); }
        .login-brand p {
            color: rgba(255,255,255,0.5);
            font-size: 13px;
            margin-top: 6px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Carte login */
        .login-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 40px 36px;
        }

        /* Erreur */
        .error-box {
            background: rgba(192,57,43,0.15);
            border: 1px solid rgba(192,57,43,0.4);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-box i { color: var(--error); font-size: 16px; }
        .error-box p { color: #f1948a; font-size: 13px; }

        /* Formulaire */
        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.3);
            font-size: 15px;
            width: 16px;
            text-align: center;
        }
        .form-group input {
            width: 100%;
            padding: 14px 18px 14px 44px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.3); }
        .form-group input:focus {
            border-color: var(--gold);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.15);
        }

        /* Bouton */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: var(--primary-dark);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: var(--gold-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(212,175,55,0.3);
        }
        .btn-login:active { transform: translateY(0); }

        /* Lien retour */
        .back-link {
            text-align: center;
            margin-top: 28px;
        }
        .back-link a {
            color: rgba(255,255,255,0.45);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link a:hover { color: var(--gold); }
        .back-link a i { margin-right: 6px; }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card { padding: 30px 24px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Branding -->
        <div class="login-brand">
            <div class="logo-icon">
                <i class="fas fa-building"></i>
            </div>
            <h1>H&M <span>Immobilier</span></h1>
            <p>Espace Administration</p>
        </div>

        <!-- Carte de connexion -->
        <div class="login-card">
            <?php if ($error): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= htmlspecialchars($error, ENT_QUOTES | ENT_HTML5) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login.php" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               placeholder="votre@email.com"
                               autocomplete="email"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>
        </div>

        <!-- Lien retour -->
        <div class="back-link">
            <a href="/index.html"><i class="fas fa-arrow-left"></i>Retour au site</a>
        </div>
    </div>
</body>
</html>
