<?php
require_once __DIR__ . '/../includes/security.php';
session_secure_start();
if (isset($_SESSION['user_id'])) {
    audit_log($_SESSION['user_id'], 'logout', "user:{$_SESSION['user_id']}", 'Déconnexion');
}
session_logout();
header('Location: login.php');
exit;
