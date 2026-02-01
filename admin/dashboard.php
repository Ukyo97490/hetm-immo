<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/UserModel.php';
require_once __DIR__ . '/../includes/PropertyModel.php';

session_secure_start();
require_auth();

$pageTitle = 'Tableau de Bord';

// ── Récupère les statistiques ───────────────────────────
$db = getDB();

$stats = [
    'total_properties' => (int)$db->query("SELECT COUNT(*) as c FROM properties")->fetch()['c'],
    'active_properties' => (int)$db->query("SELECT COUNT(*) as c FROM properties WHERE status = 'active'")->fetch()['c'],
    'sold_properties'   => (int)$db->query("SELECT COUNT(*) as c FROM properties WHERE status = 'sold'")->fetch()['c'],
    'total_agents'      => (int)$db->query("SELECT COUNT(*) as c FROM users WHERE role = 'agent' AND is_active = 1")->fetch()['c'],
    'total_admins'      => (int)$db->query("SELECT COUNT(*) as c FROM users WHERE role = 'admin' AND is_active = 1")->fetch()['c'],
];

// Derniers biens ajoutés
$recentStmt = $db->prepare(
    "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) as manager_name
     FROM properties p
     LEFT JOIN users u ON p.managed_by = u.id
     ORDER BY p.created_at DESC LIMIT 6"
);
$recentStmt->execute();
$recentProperties = $recentStmt->fetchAll();

// Derniers logs audit
$auditStmt = $db->prepare(
    "SELECT al.*, u.email as user_email
     FROM audit_log al
     LEFT JOIN users u ON al.user_id = u.id
     ORDER BY al.created_at DESC LIMIT 8"
);
$auditStmt->execute();
$recentAudit = $auditStmt->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>

<!-- ── Stats Grid ──────────────────────────────────────── -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-home"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_properties'] ?></div>
            <div class="stat-label">Biens au total</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['active_properties'] ?></div>
            <div class="stat-label">Biens actifs</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-handshake"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['sold_properties'] ?></div>
            <div class="stat-label">Biens vendus</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_agents'] + $stats['total_admins'] ?></div>
            <div class="stat-label">Utilisateurs actifs</div>
        </div>
    </div>
</div>

<!-- ── Derniers Biens ──────────────────────────────────── -->
<div class="table-wrapper" style="margin-bottom: 28px;">
    <div class="table-header">
        <h3><i class="fas fa-home" style="color: var(--primary); margin-right: 8px;"></i>Derniers Biens Ajoutés</h3>
        <a href="/admin/properties.php" class="btn btn-outline btn-sm">Voir tous</a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Lieu</th>
                    <th>Prix</th>
                    <th>Gestionnaire</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentProperties as $p): ?>
                <tr>
                    <td>
                        <a href="/admin/property-form.php?edit=<?= (int)$p['id'] ?>"
                           style="color: var(--primary); font-weight: 500;">
                            <?= htmlspecialchars($p['title'], ENT_QUOTES | ENT_HTML5) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($p['property_type']), ENT_QUOTES | ENT_HTML5) ?></td>
                    <td><?= htmlspecialchars($p['location'], ENT_QUOTES | ENT_HTML5) ?></td>
                    <td style="font-weight: 600; color: var(--gold);">
                        <?= number_format((float)$p['price'], 0, ',', ' ') ?> €
                    </td>
                    <td><?= htmlspecialchars($p['manager_name'], ENT_QUOTES | ENT_HTML5) ?></td>
                    <td>
                        <span class="badge badge-<?= $p['status'] ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentProperties)): ?>
                <tr><td colspan="6" style="text-align: center; color: var(--gray-400); padding: 30px;">Aucun bien pour le moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Derniers Logs Audit (si admin) ──────────────────── -->
<?php if (is_admin()): ?>
<div class="table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-shield-alt" style="color: var(--primary); margin-right: 8px;"></i>Journal d'Audit Récent</h3>
        <a href="/admin/audit.php" class="btn btn-outline btn-sm">Voir complet</a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Cible</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentAudit as $log): ?>
                <tr>
                    <td style="font-size: 12px; color: var(--gray-500);">
                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                    </td>
                    <td style="font-size: 13px;">
                        <?= htmlspecialchars($log['user_email'] ?? '— système', ENT_QUOTES | ENT_HTML5) ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= str_contains($log['action'], 'fail') ? 'inactive' : 'active' ?>">
                            <?= htmlspecialchars($log['action'], ENT_QUOTES | ENT_HTML5) ?>
                        </span>
                    </td>
                    <td style="font-size: 12px; color: var(--gray-500);">
                        <?= htmlspecialchars($log['target'] ?? '—', ENT_QUOTES | ENT_HTML5) ?>
                    </td>
                    <td style="font-size: 12px; color: var(--gray-400);">
                        <?= htmlspecialchars($log['ip_addr'], ENT_QUOTES | ENT_HTML5) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Modal de confirmation (partagé) ─────────────────── -->
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
