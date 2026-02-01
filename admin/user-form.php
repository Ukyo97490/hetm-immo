<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();
require_admin();  // Admin uniquement

$model  = new UserModel();
$editId = sanitize_int($_GET['edit'] ?? null);
$isEditing = ($editId !== null);
$user   = null;
$errors = [];

// ── Charge l'utilisateur si mode édition ────────────────
if ($isEditing) {
    $user = $model->findById($editId);
    if ($user === null) {
        $_SESSION['flash_error'] = 'Utilisateur introuvable.';
        header('Location: /admin/users.php');
        exit;
    }
    // On ne peut pas se modifier soi-même via cette page (il y a le profil pour ça)
    if ((int)$editId === (int)$_SESSION['user_id']) {
        $_SESSION['flash_error'] = 'Utilisez la page Profil pour vous modifier.';
        header('Location: /admin/users.php');
        exit;
    }
}

// ── Traitement POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email     = sanitize_email($_POST['email'] ?? '');
    $firstName = sanitize_string($_POST['first_name'] ?? '', 100);
    $lastName  = sanitize_string($_POST['last_name'] ?? '', 100);
    $role      = $_POST['role'] ?? 'agent';
    $password  = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // ── Validation ────────────────────────────────────
    if ($email === false)   $errors[] = 'Email invalide.';
    if (empty($firstName))  $errors[] = 'Le prénom est obligatoire.';
    if (empty($lastName))   $errors[] = 'Le nom est obligatoire.';
    if (!in_array($role, ['admin', 'agent'], true)) $errors[] = 'Rôle invalide.';

    // Validation mot de passe (obligatoire à la création, optionnel à l'édition)
    if (!$isEditing) {
        if (empty($password)) {
            $errors[] = 'Le mot de passe est obligatoire à la création.';
        } else {
            $pwErrors = validate_password($password);
            $errors   = array_merge($errors, $pwErrors);

            if (empty($pwErrors) && $password !== $passwordConfirm) {
                $errors[] = 'Les deux mots de passe ne correspondent pas.';
            }
        }
    } else {
        // À l'édition : si un mot de passe est fourni, il doit être valide
        if ($password !== '') {
            $pwErrors = validate_password($password);
            $errors   = array_merge($errors, $pwErrors);

            if (empty($pwErrors) && $password !== $passwordConfirm) {
                $errors[] = 'Les deux mots de passe ne correspondent pas.';
            }
        }
    }

    // Vérifie unicité de l'email (sauf si c'est le même utilisateur en édition)
    if (empty($errors) && $email !== false) {
        $existingUser = $model->findByEmail($email);
        if ($existingUser !== null && (!$isEditing || (int)$existingUser['id'] !== $editId)) {
            $errors[] = 'Cet email est déjà utilisé par un autre compte.';
        }
    }

    // ── Sauvegarde ──────────────────────────────────────
    if (empty($errors)) {
        if ($isEditing) {
            // Met à jour les champs
            $model->update($editId, [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'role'       => $role,
            ]);

            // Met à jour l'email via une requête directe (non dans whitelist update pour la sécurité)
            $db = getDB();
            $db->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$email, $editId]);

            // Met à jour le mot de passe si fourni
            if ($password !== '') {
                $model->updatePassword($editId, $password);
                audit_log($_SESSION['user_id'], 'change_password', "user:$editId", 'Mot de passe changé par admin');
            }

            audit_log($_SESSION['user_id'], 'update_user', "user:$editId", 'Utilisateur modifié');
            $_SESSION['flash_success'] = 'Utilisateur mis à jour avec succès.';
        } else {
            // Création
            $newId = $model->create($email, $password, $firstName, $lastName, $role);
            if ($newId === false) {
                $errors[] = 'Erreur lors de la création (email déjà utilisé).';
            } else {
                audit_log($_SESSION['user_id'], 'create_user', "user:$newId", "Utilisateur créé: $email (rôle: $role)");
                $_SESSION['flash_success'] = 'Utilisateur créé avec succès.';
                header('Location: /admin/users.php');
                exit;
            }
        }

        if (empty($errors)) {
            header('Location: /admin/users.php');
            exit;
        }
    }
}

// ── Valeurs pour le formulaire ──────────────────────────
$val = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $val = $_POST;
} elseif ($isEditing && $user) {
    $val = $user;
} else {
    $val = ['role' => 'agent'];
}

$pageTitle = $isEditing ? 'Modifier Utilisateur' : 'Créer Utilisateur';

include __DIR__ . '/includes/admin_header.php';
?>

<!-- ── Erreurs ──────────────────────────────────────────── -->
<?php if (!empty($errors)): ?>
<div style="background: var(--error-bg); border: 1px solid rgba(192,57,43,0.25); border-radius: var(--radius); padding: 16px 20px; margin-bottom: 20px;">
    <p style="color: var(--error); font-weight: 600; font-size: 14px; margin-bottom: 8px;"><i class="fas fa-exclamation-circle"></i> Erreurs</p>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($errors as $e): ?>
        <li style="color: #922b21; font-size: 13px; padding: 2px 0;">• <?= htmlspecialchars($e, ENT_QUOTES | ENT_HTML5) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ── Formulaire ─────────────────────────────────────── -->
<div class="form-card">
    <h2><?= $isEditing ? 'Modifier l\'Utilisateur' : 'Créer un Nouvel Utilisateur' ?></h2>
    <p class="form-subtitle">
        <?= $isEditing
            ? 'Modifiez les informations de cet utilisateur. Laissez les champs mot de passe vides pour ne pas changer.'
            : 'Remplissez les informations pour créer un nouveau compte. L\'administrateur définit le rôle.' ?>
    </p>

    <form method="POST" action="/admin/user-form.php<?= $isEditing ? '?edit=' . (int)$editId : '' ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <!-- Prénom -->
            <div class="form-group">
                <label>Prénom <span class="req">*</span></label>
                <input type="text" name="first_name"
                       value="<?= htmlspecialchars($val['first_name'] ?? '', ENT_QUOTES | ENT_HTML5) ?>"
                       placeholder="Jean" required>
            </div>

            <!-- Nom -->
            <div class="form-group">
                <label>Nom <span class="req">*</span></label>
                <input type="text" name="last_name"
                       value="<?= htmlspecialchars($val['last_name'] ?? '', ENT_QUOTES | ENT_HTML5) ?>"
                       placeholder="Dupont" required>
            </div>

            <!-- Email -->
            <div class="form-group full">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($val['email'] ?? '', ENT_QUOTES | ENT_HTML5) ?>"
                       placeholder="jean.dupont@email.com" required>
                <p class="hint">Utilisé comme identifiant de connexion. Doit être unique.</p>
            </div>

            <!-- Rôle (radio buttons) -->
            <div class="form-group full">
                <label>Rôle <span class="req">*</span></label>
                <div class="radio-row">
                    <label class="radio-item <?= ($val['role'] ?? '') === 'agent' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="agent" <?= ($val['role'] ?? '') === 'agent' ? 'checked' : '' ?>>
                        <div>
                            <div class="radio-label"><i class="fas fa-user" style="color: var(--info); margin-right: 8px;"></i>Agent</div>
                            <div class="radio-desc">Peut gérer ses propres biens uniquement.</div>
                        </div>
                    </label>
                    <label class="radio-item <?= ($val['role'] ?? '') === 'admin' ? 'selected' : '' ?>">
                        <input type="radio" name="role" value="admin" <?= ($val['role'] ?? '') === 'admin' ? 'checked' : '' ?>>
                        <div>
                            <div class="radio-label"><i class="fas fa-crown" style="color: var(--gold); margin-right: 8px;"></i>Administrateur</div>
                            <div class="radio-desc">Accès complet, gestion de tous les biens et utilisateurs.</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label>Mot de Passe <?php if (!$isEditing): ?><span class="req">*</span><?php endif; ?></label>
                <input type="password" name="password" placeholder="••••••••"
                       <?= !$isEditing ? 'required' : '' ?>>
                <p class="hint">Min. 8 car., une majuscule, une minuscule, un chiffre.</p>
            </div>

            <!-- Confirmation mot de passe -->
            <div class="form-group">
                <label>Confirmation Mot de Passe <?php if (!$isEditing): ?><span class="req">*</span><?php endif; ?></label>
                <input type="password" name="password_confirm" placeholder="••••••••"
                       <?= !$isEditing ? 'required' : '' ?>>
                <?php if ($isEditing): ?>
                <p class="hint">Laissez vide pour ne pas modifier le mot de passe.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <a href="/admin/users.php" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-gold">
                <i class="fas fa-<?= $isEditing ? 'save' : 'user-plus' ?>"></i>
                <?= $isEditing ? 'Mettre à jour' : 'Créer le Compte' ?>
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
