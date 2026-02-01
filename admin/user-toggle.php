<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Location: users.php'); exit; }
csrf_check();

$userId = sanitize_int($_POST['user_id'] ?? null);
$action  = $_POST['action'] ?? '';

if ($userId === null || !in_array($action, ['activate','deactivate'], true)) { $_SESSION['flash_error']='Requête invalide.'; header('Location: users.php'); exit; }
if ((int)$userId === (int)$_SESSION['user_id']) { $_SESSION['flash_error']='Vous ne pouvez pas désactiver votre propre compte.'; header('Location: users.php'); exit; }

$model = new UserModel();
$user  = $model->findById($userId);
if ($user === null) { $_SESSION['flash_error']='Utilisateur introuvable.'; header('Location: users.php'); exit; }

$success = ($action === 'activate') ? $model->activate($userId) : $model->deactivate($userId);
if ($success) {
    $label = ($action === 'activate') ? 'activé' : 'désactivé';
    audit_log($_SESSION['user_id'], $action.'_user', "user:$userId", "Utilisateur {$user['email']} $label");
    $_SESSION['flash_success'] = "Utilisateur « {$user['first_name']} {$user['last_name']} » $label avec succès.";
} else { $_SESSION['flash_error'] = 'Erreur lors de la modification.'; }

header('Location: users.php'); exit;
