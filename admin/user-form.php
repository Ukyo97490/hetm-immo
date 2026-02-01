<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();
require_admin();

$model     = new UserModel();
$editId    = sanitize_int($_GET['edit'] ?? null);
$isEditing = ($editId !== null);
$user      = null;
$errors    = [];

if ($isEditing) {
    $user = $model->findById($editId);
    if ($user === null) { $_SESSION['flash_error']='Utilisateur introuvable.'; header('Location: users.php'); exit; }
    if ((int)$editId === (int)$_SESSION['user_id']) { $_SESSION['flash_error']='Utilisez la page Profil pour vous modifier.'; header('Location: users.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email           = sanitize_email($_POST['email'] ?? '');
    $firstName       = sanitize_string($_POST['first_name'] ?? '', 100);
    $lastName        = sanitize_string($_POST['last_name'] ?? '', 100);
    $role            = $_POST['role'] ?? 'agent';
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($email === false)   $errors[] = 'Email invalide.';
    if (empty($firstName))  $errors[] = 'Le prénom est obligatoire.';
    if (empty($lastName))   $errors[] = 'Le nom est obligatoire.';
    if (!in_array($role, ['admin','agent'], true)) $errors[] = 'Rôle invalide.';

    if (!$isEditing) {
        if (empty($password)) { $errors[] = 'Le mot de passe est obligatoire à la création.'; }
        else {
            $pwErrors = validate_password($password);
            $errors   = array_merge($errors, $pwErrors);
            if (empty($pwErrors) && $password !== $passwordConfirm) $errors[] = 'Les deux mots de passe ne correspondent pas.';
        }
    } else {
        if ($password !== '') {
            $pwErrors = validate_password($password);
            $errors   = array_merge($errors, $pwErrors);
            if (empty($pwErrors) && $password !== $passwordConfirm) $errors[] = 'Les deux mots de passe ne correspondent pas.';
        }
    }

    if (empty($errors) && $email !== false) {
        $existingUser = $model->findByEmail($email);
        if ($existingUser !== null && (!$isEditing || (int)$existingUser['id'] !== $editId))
            $errors[] = 'Cet email est déjà utilisé par un autre compte.';
    }

    if (empty($errors)) {
        if ($isEditing) {
            $model->update($editId, ['first_name'=>$firstName,'last_name'=>$lastName,'role'=>$role]);
            $db = getDB();
            $db->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$email, $editId]);
            if ($password !== '') { $model->updatePassword($editId, $password); audit_log($_SESSION['user_id'], 'change_password', "user:$editId", 'Mot de passe changé par admin'); }
            audit_log($_SESSION['user_id'], 'update_user', "user:$editId", 'Utilisateur modifié');
            $_SESSION['flash_success'] = 'Utilisateur mis à jour avec succès.';
        } else {
            $newId = $model->create($email, $password, $firstName, $lastName, $role);
            if ($newId === false) { $errors[] = 'Erreur lors de la création (email déjà utilisé).'; }
            else {
                audit_log($_SESSION['user_id'], 'create_user', "user:$newId", "Utilisateur créé: $email (rôle: $role)");
                $_SESSION['flash_success'] = 'Utilisateur créé avec succès.';
                header('Location: users.php'); exit;
            }
        }
        if (empty($errors)) { header('Location: users.php'); exit; }
    }
}

$val = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $val = $_POST; }
elseif ($isEditing && $user) { $val = $user; }
else { $val = ['role' => 'agent']; }

$pageTitle = $isEditing ? 'Modifier Utilisateur' : 'Créer Utilisateur';
include __DIR__ . '/includes/admin_header.php';
?>

<?php if (!empty($errors)): ?>
<div class="errors-block">
    <p><i class="fas fa-exclamation-circle"></i> Erreurs</p>
    <ul><?php foreach($errors as $e): ?><li>• <?= htmlspecialchars($e, ENT_QUOTES|ENT_HTML5) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="form-card">
    <h2><?= $isEditing ? 'Modifier l\'Utilisateur' : 'Créer un Nouvel Utilisateur' ?></h2>
    <p class="form-subtitle"><?= $isEditing ? 'Modifiez les informations. Laissez les champs mot de passe vides pour ne pas changer.' : 'Remplissez les informations pour créer un nouveau compte.' ?></p>

    <form method="POST" action="user-form.php<?= $isEditing ? '?edit='.(int)$editId : '' ?>" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Prénom <span class="req">*</span></label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($val['first_name']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="Jean" required>
            </div>
            <div class="form-group">
                <label>Nom <span class="req">*</span></label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($val['last_name']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="Dupont" required>
            </div>
            <div class="form-group full">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email" value="<?= htmlspecialchars($val['email']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="jean.dupont@email.com" required>
                <p class="hint">Utilisé comme identifiant de connexion. Doit être unique.</p>
            </div>
            <div class="form-group full">
                <label>Rôle <span class="req">*</span></label>
                <div class="radio-row">
                    <label class="radio-item <?= ($val['role']??'')==='agent'?'selected':'' ?>">
                        <input type="radio" name="role" value="agent" <?= ($val['role']??'')==='agent'?'checked':'' ?>>
                        <div><div class="radio-label"><i class="fas fa-user" style="color:var(--info);margin-right:8px;"></i>Agent</div><div class="radio-desc">Peut gérer ses propres biens uniquement.</div></div>
                    </label>
                    <label class="radio-item <?= ($val['role']??'')==='admin'?'selected':'' ?>">
                        <input type="radio" name="role" value="admin" <?= ($val['role']??'')==='admin'?'checked':'' ?>>
                        <div><div class="radio-label"><i class="fas fa-crown" style="color:var(--gold);margin-right:8px;"></i>Administrateur</div><div class="radio-desc">Accès complet, gestion de tous les biens et utilisateurs.</div></div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Mot de Passe <?php if (!$isEditing): ?><span class="req">*</span><?php endif; ?></label>
                <input type="password" name="password" placeholder="••••••••" <?= !$isEditing?'required':'' ?>>
                <p class="hint">Min. 8 car., une majuscule, une minuscule, un chiffre.</p>
            </div>
            <div class="form-group">
                <label>Confirmation Mot de Passe <?php if (!$isEditing): ?><span class="req">*</span><?php endif; ?></label>
                <input type="password" name="password_confirm" placeholder="••••••••" <?= !$isEditing?'required':'' ?>>
                <?php if ($isEditing): ?><p class="hint">Laissez vide pour ne pas modifier le mot de passe.</p><?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <a href="users.php" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-gold"><i class="fas fa-<?= $isEditing?'save':'user-plus' ?>"></i> <?= $isEditing?'Mettre à jour':'Créer le Compte' ?></button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
