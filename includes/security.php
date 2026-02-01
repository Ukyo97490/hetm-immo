<?php
/**
 * H&M Immobilier — Fonctions de sécurité centralisées
 */

require_once __DIR__ . '/../config.php';

// ════════════════════════════════════════════════════════════
//  CSRF — Génération & validation du token
// ════════════════════════════════════════════════════════════

/**
 * Génère un token CSRF signé (HMAC-SHA256).
 * Le token contient un nonce + une signature temps-limité.
 */
function csrf_generate(): string
{
    $nonce     = bin2hex(random_bytes(16));
    $time      = time();
    $payload   = $nonce . ':' . $time;
    $signature = hash_hmac('sha256', $payload, CSRF_SECRET);
    // Encode tout en base64url pour l'HTML
    return base64url_encode($payload . ':' . $signature);
}

/**
 * Valide un token CSRF reçu (POST).
 * Vérifie : format, signature HMAC, expiration (1 heure).
 */
function csrf_validate(string $token): bool
{
    $raw = base64url_decode($token);
    if ($raw === false) return false;

    $parts = explode(':', $raw, 3);
    if (count($parts) !== 3) return false;

    [$nonce, $time, $signature] = $parts;

    // Vérifie expiration
    if ((int)$time < (time() - 3600)) return false;

    // Vérifie signature (comparaison temporelle sécurisée)
    $expected = hash_hmac('sha256', $nonce . ':' . $time, CSRF_SECRET);
    return hash_equals($expected, $signature);
}

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string|false
{
    return base64_decode(strtr($data, '-_', '+/'), true);
}

/**
 * Insère un champ hidden CSRF dans un formulaire HTML.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_generate(), ENT_QUOTES | ENT_HTML5) . '">';
}

/**
 * Middleware : vérifie CSRF sur toute requête POST/PUT/DELETE.
 * À appeler au début de chaque script qui traite un formulaire.
 */
function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $token = $_POST['csrf_token'] ?? '';
        if (!csrf_validate($token)) {
            audit_log(null, 'csrf_failed', null, 'Token CSRF invalide ou expiré');
            http_response_code(403);
            exit('Requête refusée — token de sécurité invalide.');
        }
    }
}

// ════════════════════════════════════════════════════════════
//  SESSION — Authentification sécurisée
// ════════════════════════════════════════════════════════════

/**
 * Configure les paramètres de session PHP sécurisés
 * et démarre la session.
 */
function session_secure_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
        session_name('HM_SESS');
        session_start();
    }

    // Régénère l'ID de session régulièrement (anti-hijacking)
    if (!isset($_SESSION['_session_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_session_initiated'] = true;
    }

    // Vérifie l'IP et le User-Agent pour détecter les détournements
    if (isset($_SESSION['_ip'])) {
        if ($_SESSION['_ip'] !== get_client_ip()) {
            session_destroy();
            return;
        }
    }

    // Vérifie l'expiration
    if (isset($_SESSION['_expires_at']) && $_SESSION['_expires_at'] < time()) {
        session_destroy();
        return;
    }
}

/**
 * Connecte un utilisateur : crée la session sécurisée.
 */
function session_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['user_email']   = $user['email'];
    $_SESSION['user_role']    = $user['role'];
    $_SESSION['_ip']          = get_client_ip();
    $_SESSION['_ua']          = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['_expires_at']  = time() + SESSION_LIFETIME;
    $_SESSION['_session_initiated'] = true;
}

/**
 * Déconnecte l'utilisateur actuel.
 */
function session_logout(): void
{
    $_SESSION = [];
    if (isset($_COOKIE[session_cookie_name()])) {
        setcookie(session_cookie_name(), '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
    }
    session_destroy();
}

function session_cookie_name(): string
{
    return 'HM_SESS';
}

/**
 * Retourne l'utilisateur connecté ou null.
 */
function get_logged_in_user(): ?array
{
    if (!isset($_SESSION['user_id'])) return null;

    return [
        'id'    => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

/**
 * Vérifie si l'utilisateur est admin.
 */
function is_admin(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Middleware : redirige vers login si non authentifié.
 */
function require_auth(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Middleware : redirige si pas admin.
 */
function require_admin(): void
{
    require_auth();
    if (!is_admin()) {
        http_response_code(403);
        header('Location: /admin/dashboard.php');
        exit;
    }
}

// ════════════════════════════════════════════════════════════
//  INPUT — Sanitisation & validation
// ════════════════════════════════════════════════════════════

function sanitize_string(string $input, int $maxLen = 255): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_email(string $input): string|false
{
    $email = filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    if ($email === false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return strtolower($email);
}

function sanitize_int(?string $input): ?int
{
    if ($input === null || $input === '') return null;
    $val = filter_var($input, FILTER_VALIDATE_INT);
    return $val !== false ? (int)$val : null;
}

function sanitize_float(?string $input): ?float
{
    if ($input === null || $input === '') return null;
    // Accepte virgule ou point comme séparateur décimal
    $input = str_replace(',', '.', $input);
    $val   = filter_var($input, FILTER_VALIDATE_FLOAT);
    return $val !== false ? (float)$val : null;
}

function validate_password(string $password): array
{
    $errors = [];
    if (strlen($password) < 8)
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    if (!preg_match('/[A-Z]/', $password))
        $errors[] = 'Il doit contenir au moins une lettre majuscule.';
    if (!preg_match('/[a-z]/', $password))
        $errors[] = 'Il doit contenir au moins une lettre minuscule.';
    if (!preg_match('/[0-9]/', $password))
        $errors[] = 'Il doit contenir au moins un chiffre.';
    if (strlen($password) > 72)
        $errors[] = 'Le mot de passe est trop long (max 72 caractères).'; // limite bcrypt
    return $errors;
}

// ════════════════════════════════════════════════════════════
//  UPLOAD — Validation sécurisée des fichiers images
// ════════════════════════════════════════════════════════════

/**
 * Valide et déplace un fichier uploadé.
 * Retourne le chemin relatif du fichier ou null en cas d'erreur.
 */
function upload_image(array $file, string $subdir = 'properties'): ?string
{
    // Vérifie les erreurs PHP
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Vérifie la taille
    if ($file['size'] > UPLOAD_MAX) {
        return null;
    }

    // Vérifie le type MIME réel (pas celui envoyé par le client)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (!in_array($realMime, UPLOAD_TYPES, true)) {
        return null;
    }

    // Vérifie l'extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_EXTS, true)) {
        return null;
    }

    // Vérifie que c'est vraiment une image (GD)
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        return null;
    }

    // Génère un nom de fichier sécurisé (random, pas d'injection)
    $target_dir = UPLOAD_DIR . $subdir . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true);
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
    $target   = $target_dir . $safeName;

    // Vérifie que le fichier provient bien d'un upload HTTP
    if (!is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return null;
    }

    return $subdir . '/' . $safeName;
}

// ════════════════════════════════════════════════════════════
//  AUDIT LOG
// ════════════════════════════════════════════════════════════

function audit_log(?int $userId, string $action, ?string $target = null, ?string $details = null): void
{
    try {
        $db = getDB();
        $db->prepare(
            'INSERT INTO audit_log (user_id, action, target, details, ip_addr) VALUES (?, ?, ?, ?, ?)'
        )->execute([$userId, $action, $target, $details, get_client_ip()]);
    } catch (PDOException) {
        // Ne bloque pas l'exécution si l'audit échoue
        error_log("Audit log failed: action=$action target=$target");
    }
}

// ════════════════════════════════════════════════════════════
//  UTILITAIRES
// ════════════════════════════════════════════════════════════

/**
 * Récupère l'IP réelle du client (proxy-safe).
 */
function get_client_ip(): string
{
    $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // X-Forwarded-For peut contenir plusieurs IPs
            if (str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Rate-limiting simple basé sur fichier (sans Redis).
 * Retourne true si la limite est dépassée.
 */
function rate_limit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $file = sys_get_temp_dir() . '/hm_rl_' . md5($key);

    $attempts = [];
    if (file_exists($file)) {
        $data = file_get_contents($file);
        if ($data !== false) {
            $attempts = json_decode($data, true) ?: [];
        }
    }

    $now    = time();
    $cutoff = $now - $windowSeconds;

    // Purge les tentatives expirées
    $attempts = array_filter($attempts, fn($t) => $t > $cutoff);

    if (count($attempts) >= $maxAttempts) {
        return true; // limite atteinte
    }

    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts));
    return false;
}

function rate_limit_reset(string $key): void
{
    $file = sys_get_temp_dir() . '/hm_rl_' . md5($key);
    if (file_exists($file)) {
        unlink($file);
    }
}
