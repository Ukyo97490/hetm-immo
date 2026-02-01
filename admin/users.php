<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();
require_admin();  // Admin uniquement

$pageTitle = 'Gestion des Utilisateurs';
$model     = new UserModel();
$page      = max(1, sanitize_int($_GET['page'] ?? '1') ?? 1);
$result    = $model->findAll($page, 15);

include __DIR__ . '/includes/admin_header.php';
?>

<!-- ── Barre d'actions ─────────────────────────────────── -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <span style="font-size: 14px; color: var(--gray-500);">
            <?= $result['total'] ?> utilisateur<?= $result['total'] !== 1 ? 's' : '' ?>
        </span>
    </div>
    <a href="/admin/user-form.php" class="btn btn-gold">
        <i class="fas fa-user-plus"></i> Créer un Utilisateur
    </a>
</div>

<!-- ── Table ──────────────────────────────────────────── -->
<div class="table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-users" style="color: var(--primary); margin-right: 8px;"></i>Liste des Utilisateurs</h3>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['users'] as $u): ?>
                <tr>
                    <td style="color: var(--gray-400); font-size: 13px;"><?= (int)$u['id'] ?></td>
                    <td style="font-weight: 500;">
                        <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name'], ENT_QUOTES | ENT_HTML5) ?>
                    </td>
                    <td style="font-size: 13px; color: var(--gray-600);">
                        <?= htmlspecialchars($u['email'], ENT_QUOTES | ENT_HTML5) ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $u['role'] ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $u['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $u['is_active'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td style="font-size: 12px; color: var(--gray-500);">
                        <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <!-- Modifier -->
                            <a href="/admin/user-form.php?edit=<?= (int)$u['id'] ?>" class="btn-icon edit" title="Modifier">
                                <i class="fas fa-pencil-alt"></i>
                            </a>

                            <!-- Activer / Désactiver -->
                            <?php
                            // On ne peut pas désactiver son propre compte
                            $isSelf = ((int)$u['id'] === (int)$_SESSION['user_id']);
                            ?>
                            <?php if (!$isSelf): ?>
                            <button class="btn-icon toggle"
                                    title="<?= $u['is_active'] ? 'Désactiver' : 'Activer' ?>"
                                    onclick="
                                        confirmAction(
                                            '<?= $u['is_active']
                                                ? 'Désactiver ' . htmlspecialchars($u['first_name'], ENT_QUOTES | ENT_HTML5) . ' ? Il ne pourra plus se connecter.'
                                                : 'Activer ' . htmlspecialchars($u['first_name'], ENT_QUOTES | ENT_HTML5) . ' ? Il pourra se connecter à nouveau.' ?>',
                                            () => {
                                                const f = document.createElement('form');
                                                f.method = 'POST';
                                                f.action = '/admin/user-toggle.php';
                                                f.innerHTML =
                                                    '<input type=\"hidden\" name=\"csrf_token\" value=\"<?= htmlspecialchars(csrf_generate(), ENT_QUOTES | ENT_HTML5) ?>\">' +
                                                    '<input type=\"hidden\" name=\"user_id\" value=\"<?= (int)$u['id'] ?>\">' +
                                                    '<input type=\"hidden\" name=\"action\" value=\"<?= $u['is_active'] ? 'deactivate' : 'activate' ?>\">';
                                                document.body.appendChild(f);
                                                f.submit();
                                            }
                                        );
                                    ">
                                <i class="fas fa-<?= $u['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn-icon toggle" disabled style="opacity: 0.3; cursor: not-allowed;" title="Vous ne pouvez pas désactiver votre propre compte">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($result['users'])): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--gray-400); padding: 40px;">
                        Aucun utilisateur.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Pagination ──────────────────────────────────────── -->
<?php if ($result['last_page'] > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/admin/users.php?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $result['last_page']; $i++): ?>
        <a href="/admin/users.php?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $result['last_page']): ?>
        <a href="/admin/users.php?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <div class="modal-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
        <h3>Confirmation</h3>
        <p>Êtes-vous sûr ?</p>
        <div class="modal-actions">
            <button class="btn btn-outline btn-sm" id="modalCancelBtn">Annuler</button>
            <button class="btn btn-danger btn-sm" id="modalConfirmBtn">Confirmer</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
