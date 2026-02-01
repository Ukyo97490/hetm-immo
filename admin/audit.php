<?php
require_once __DIR__ . '/../includes/security.php';

session_secure_start();
require_admin();

$pageTitle   = 'Journal d\'Audit';
$db          = getDB();
$page        = max(1, sanitize_int($_GET['page'] ?? '1') ?? 1);
$perPage     = 20;
$offset      = ($page - 1) * $perPage;
$filterAction = $_GET['action'] ?? '';

$conditions = [];
$params     = [];
if ($filterAction !== '') { $conditions[] = 'action = ?'; $params[] = $filterAction; }
$where = !empty($conditions) ? 'WHERE '.implode(' AND ', $conditions) : '';

$countStmt = $db->prepare("SELECT COUNT(*) as c FROM audit_log $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];

$params2 = $params;
$params2[] = $perPage;
$params2[] = $offset;
$stmt = $db->prepare("SELECT al.*, u.email as user_email, u.first_name, u.last_name FROM audit_log al LEFT JOIN users u ON al.user_id = u.id $where ORDER BY al.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute($params2);
$logs = $stmt->fetchAll();
$lastPage = max(1, (int)ceil($total / $perPage));

$actionsStmt = $db->query("SELECT DISTINCT action FROM audit_log ORDER BY action");
$allActions  = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/admin_header.php';
?>

<!-- Filters -->
<div class="table-wrapper" style="margin-bottom:20px;">
    <div style="padding:16px 24px;">
        <form method="GET" action="audit.php" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="min-width:200px;">
                <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:5px;">Filtrer par Action</label>
                <select name="action" style="padding:9px 12px;border:1px solid var(--gray-300);border-radius:6px;font-size:14px;outline:none;background:white;width:100%;">
                    <option value="">Toutes les actions</option>
                    <?php foreach ($allActions as $a): ?>
                    <option value="<?= htmlspecialchars($a, ENT_QUOTES|ENT_HTML5) ?>" <?= $filterAction===$a?'selected':'' ?>><?= htmlspecialchars($a, ENT_QUOTES|ENT_HTML5) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:9px 20px;"><i class="fas fa-search"></i> Filtrer</button>
            <?php if ($filterAction): ?><a href="audit.php" class="btn btn-outline" style="padding:9px 16px;font-size:13px;"><i class="fas fa-times"></i> Réinitialiser</a><?php endif; ?>
        </form>
    </div>
</div>

<!-- Table -->
<div class="table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:8px;"></i>Journal d'Audit</h3>
        <span style="font-size:13px;color:var(--gray-500);"><?= $total ?> entrée<?= $total!==1?'s':'' ?></span>
    </div>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Date & Heure</th><th>Utilisateur</th><th>Action</th><th>Cible</th><th>Détails</th><th>IP</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <?php
                $bc = 'badge-active';
                if (str_contains($log['action'],'fail')||str_contains($log['action'],'denied')) $bc='badge-inactive';
                elseif (str_contains($log['action'],'delete')) $bc='badge-sold';
                ?>
                <tr>
                    <td style="font-size:12px;color:var(--gray-500);white-space:nowrap;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                    <td style="font-size:13px;">
                        <?php if ($log['user_email']): ?>
                            <strong style="color:var(--dark);"><?= htmlspecialchars($log['first_name'].' '.$log['last_name'], ENT_QUOTES|ENT_HTML5) ?></strong>
                            <br><span style="font-size:11px;color:var(--gray-400);"><?= htmlspecialchars($log['user_email'], ENT_QUOTES|ENT_HTML5) ?></span>
                        <?php else: ?><span style="color:var(--gray-400);">— Système</span><?php endif; ?>
                    </td>
                    <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($log['action'], ENT_QUOTES|ENT_HTML5) ?></span></td>
                    <td style="font-size:12px;color:var(--gray-500);font-family:monospace;"><?= htmlspecialchars($log['target']??'—', ENT_QUOTES|ENT_HTML5) ?></td>
                    <td style="font-size:12px;color:var(--gray-500);max-width:200px;"><?= htmlspecialchars($log['details']??'—', ENT_QUOTES|ENT_HTML5) ?></td>
                    <td style="font-size:12px;color:var(--gray-400);font-family:monospace;"><?= htmlspecialchars($log['ip_addr'], ENT_QUOTES|ENT_HTML5) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--gray-400);padding:40px;">Aucune entrée dans le journal.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($lastPage > 1): ?>
<div class="pagination-admin">
    <?php $pf = $filterAction ? 'action='.urlencode($filterAction).'&' : ''; ?>
    <?php if ($page > 1): ?><a href="audit.php?<?= $pf ?>page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($i=1; $i<=$lastPage; $i++): ?>
    <a href="audit.php?<?= $pf ?>page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $lastPage): ?><a href="audit.php?<?= $pf ?>page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
