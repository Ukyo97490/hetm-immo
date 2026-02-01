<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();
require_auth();

$pageTitle = 'Mon Profil';
$model     = new UserModel();
$user      = $model->findByEmail($_SESSION['user_email']);
$errors    = [];
$success   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword)) { $errors[] = 'Le mot de passe actuel est requis.'; }
    elseif (!password_verify($currentPassword, $user['password_hash'])) {
        $errors[] = 'Le mot de passe actuel est incorrect.';
        audit_log($_SESSION['user_id'], 'profile_password_fail', "user:{$_SESSION['user_id']}", 'Mot de passe actuel incorrect');
    }

    if (empty($errors)) {
        if (empty($newPassword)) { $errors[] = 'Le nouveau mot de passe est requis.'; }
        else {
            $pwErrors = validate_password($newPassword);
            $errors   = array_merge($errors, $pwErrors);
            if (empty($pwErrors) && $newPassword !== $confirmPassword) $errors[] = 'Les deux nouveaux mots de passe ne correspondent pas.';
        }
    }

    if (empty($errors)) {
        $model->updatePassword($_SESSION['user_id'], $newPassword);
        audit_log($_SESSION['user_id'], 'change_own_password', "user:{$_SESSION['user_id']}", 'Mot de passe changé par l\'utilisateur');
        $success = true;
    }
}

include __DIR__ . '/includes/admin_header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;max-width:860px;">

    <!-- Profile info -->
    <div class="form-card" style="max-width:100%;">
        <h2>Mes Informations</h2>
        <p class="form-subtitle">Ces informations sont gérées par un administrateur.</p>
        <div style="margin-top:24px;">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--gray-200);">
                <div style="width:56px;height:56px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:20px;font-weight:700;">
                    <?= strtoupper(substr($user['first_name']??'',0,1).substr($user['last_name']??'',0,1)) ?>
                </div>
                <div>
                    <p style="font-weight:600;font-size:16px;color:var(--dark);"><?= htmlspecialchars($user['first_name'].' '.$user['last_name'], ENT_QUOTES|ENT_HTML5) ?></p>
                    <span class="badge badge-<?= $user['role'] ?>" style="display:inline-block;margin-top:4px;"><?= ucfirst($user['role']) ?></span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">Email</label>
                    <p style="font-size:14px;color:var(--dark);margin-top:4px;"><?= htmlspecialchars($user['email'], ENT_QUOTES|ENT_HTML5) ?></p>
                </div>
                <div>
                    <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">Rôle</label>
                    <p style="font-size:14px;color:var(--dark);margin-top:4px;"><?= ucfirst($user['role']) ?></p>
                </div>
                <div>
                    <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">Compte créé le</label>
                    <p style="font-size:14px;color:var(--dark);margin-top:4px;"><?= date('d/m/Y à H:i', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Change password -->
    <div class="form-card" style="max-width:100%;">
        <h2>Changer le Mot de Passe</h2>
        <p class="form-subtitle">Utilisez cette section pour mettre à jour votre mot de passe.</p>

        <?php if ($success): ?>
        <div style="background:var(--success-bg);border:1px solid rgba(39,174,96,0.25);border-radius:var(--radius);padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-check-circle" style="color:var(--success);"></i>
            <span style="color:#1e8449;font-size:14px;">Mot de passe changé avec succès !</span>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="errors-block">
            <p><i class="fas fa-exclamation-circle"></i> Erreurs</p>
            <ul><?php foreach($errors as $e): ?><li>• <?= htmlspecialchars($e, ENT_QUOTES|ENT_HTML5) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/profile.php" novalidate>
            <?= csrf_field() ?>
            <div style="display:flex;flex-direction:column;gap:18px;margin-top:20px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:13px;font-weight:600;color:var(--gray-600);display:block;margin-bottom:6px;">Mot de Passe Actuel <span class="req">*</span></label>
                    <input type="password" name="current_password" placeholder="••••••••" required
                           style="padding:11px 14px;border:1px solid var(--gray-300);border-radius:var(--radius-sm);font-size:14px;color:var(--dark);width:100%;outline:none;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:13px;font-weight:600;color:var(--gray-600);display:block;margin-bottom:6px;">Nouveau Mot de Passe <span class="req">*</span></label>
                    <input type="password" name="new_password" placeholder="••••••••" required
                           style="padding:11px 14px;border:1px solid var(--gray-300);border-radius:var(--radius-sm);font-size:14px;color:var(--dark);width:100%;outline:none;">
                    <p class="hint">Min. 8 car., une majuscule, une minuscule, un chiffre.</p>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:13px;font-weight:600;color:var(--gray-600);display:block;margin-bottom:6px;">Confirmation <span class="req">*</span></label>
                    <input type="password" name="confirm_password" placeholder="••••••••" required
                           style="padding:11px 14px;border:1px solid var(--gray-300);border-radius:var(--radius-sm);font-size:14px;color:var(--dark);width:100%;outline:none;">
                </div>
                <button type="submit" class="btn btn-gold" style="align-self:flex-start;margin-top:8px;"><i class="fas fa-key"></i> Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
