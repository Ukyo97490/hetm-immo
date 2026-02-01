<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/PropertyModel.php';

session_secure_start();
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Location: /admin/properties.php'); exit; }
csrf_check();

$propertyId = sanitize_int($_POST['property_id'] ?? null);
if ($propertyId === null) { $_SESSION['flash_error']='ID de bien invalide.'; header('Location: /admin/properties.php'); exit; }

$model    = new PropertyModel();
$property = $model->findById($propertyId);
if ($property === null) { $_SESSION['flash_error']='Bien introuvable.'; header('Location: /admin/properties.php'); exit; }

$canDelete = is_admin() || $model->canManage($propertyId, $_SESSION['user_id']);
if (!$canDelete) {
    $_SESSION['flash_error']='Vous n\'êtes pas autorisé à supprimer ce bien.';
    audit_log($_SESSION['user_id'], 'delete_property_denied', "property:$propertyId", 'Tentative de suppression non autorisée');
    header('Location: /admin/properties.php'); exit;
}

$success = $model->delete($propertyId, $_SESSION['user_id'], is_admin());
if ($success) {
    audit_log($_SESSION['user_id'], 'delete_property', "property:$propertyId", "Bien supprimé: ".$property['title']);
    $_SESSION['flash_success'] = 'Bien « '.htmlspecialchars($property['title'], ENT_QUOTES|ENT_HTML5).' » supprimé avec succès.';
} else { $_SESSION['flash_error']='Erreur lors de la suppression.'; }

header('Location: /admin/properties.php'); exit;
