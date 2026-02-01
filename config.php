<?php
/**
 * H&M Immobilier — Configuration base de données
 * 
 * SÉCURITÉ : Ce fichier doit être HORS du répertoire web public.
 * Déplacez-le au-dessus de votre dossier htdocs/public.
 * Utilisez des variables d'environnement en production.
 */

// ── Affichage des erreurs (à DÉSACTIVER en production) ──
ini_set('display_errors', 0);
error_reporting(E_ALL);

// ── Paramètres de connexion ──────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: '127.0.0.1');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_NAME',     getenv('DB_NAME')     ?: 'hm_immobilier');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_CHARSET',  'utf8mb4');

// ── Répertoire uploads (chemin absolu, hors public/) ────
define('UPLOAD_DIR',  __DIR__ . '/uploads/');
define('UPLOAD_MAX',  5 * 1024 * 1024);          // 5 Mo max
define('UPLOAD_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_EXTS',  ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ── Sécurité sessions ────────────────────────────────────
define('SESSION_LIFETIME', 3600);    // 1 heure
define('SESSION_COOKIE_SECURE',  true);
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE','Strict');

// ── Clé de chiffrement CSRF (remplacer par une valeur aléatoire) ──
define('CSRF_SECRET', getenv('CSRF_SECRET') ?: 'replaceme_with_random_64chars_in_production');

// ── Connexion PDO singleton ──────────────────────────────
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // requêtes préparées réelles
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION time_zone = '+00:00'; SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO'"
            ]);
        } catch (PDOException $e) {
            // Ne jamais révéler les détails de connexion en production
            error_log('DB Connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Erreur interne du serveur.');
        }
    }

    return $pdo;
}
