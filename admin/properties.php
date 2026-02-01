<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/PropertyModel.php';

session_secure_start();
require_auth();

$pageTitle  = 'Biens Immobiliers';
$model      = new PropertyModel();
$page       = max(1, sanitize_int($_GET['page'] ?? '1') ?? 1);
$perPage    = 10;

$filters   = [];
$typeVal   = $_GET['type']   ?? '';
$statusVal = $_GET['status'] ?? '';
$searchVal = $_GET['search'] ?? '';

$validTypes   = ['maison','appartement','terrain','villa','chalet'];
$validStatuts = ['active','inactive','sold'];

if (in_array($typeVal, $validTypes, true))     $filters['type']   = $typeVal;
if (in_array($statusVal, $validStatuts, true)) $filters['status'] = $statusVal;
if ($searchVal !== '')                          $filters['search'] = $searchVal;

if (!is_admin()) $filters['managed_by'] = $_SESSION['user_id'];

$result = $model->findAll($filters, $page, $perPage);

include __DIR__ . '/includes/admin_header.php';
?>

<!-- Action bar -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <span style="font-size:14px;color:var(--gray-500);"><?= $result['total'] ?> bien<?= $result['total']!==1?'s':'' ?> trouvé<?= $result['total']!==1?'s':'' ?></span>
    <a href="property-form.php" class="btn btn-gold"><i class="fas fa-plus"></i> Ajouter un Bien</a>
</div>

<!-- Filters -->
<div class="table-wrapper" style="margin-bottom:20px;">
    <div style="padding:18px 24px;">
        <form method="GET" action="properties.php" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:180px;">
                <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:5px;">Recherche</label>
                <input type="text" name="search" value="<?= htmlspecialchars($searchVal, ENT_QUOTES|ENT_HTML5) ?>" placeholder="Titre, lieu..."
                       style="padding:9px 12px;border:1px solid var(--gray-300);border-radius:6px;font-size:14px;outline:none;width:100%;">
            </div>
            <div style="min-width:170px;">
                <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:5px;">Type</label>
                <select name="type" style="padding:9px 12px;border:1px solid var(--gray-300);border-radius:6px;font-size:14px;outline:none;background:white;width:100%;">
                    <option value="">Tous les types</option>
                    <?php foreach (['maison','appartement','terrain','villa','chalet'] as $t): ?>
                    <option value="<?= $t ?>" <?= $typeVal===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width:170px;">
                <label style="font-size:11px;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:5px;">Statut</label>
                <select name="status" style="padding:9px 12px;border:1px solid var(--gray-300);border-radius:6px;font-size:14px;outline:none;background:white;width:100%;">
                    <option value="">Tous les statuts</option>
                    <option value="active"   <?= $statusVal==='active'?'selected':'' ?>>Actif</option>
                    <option value="inactive" <?= $statusVal==='inactive'?'selected':'' ?>>Inactif</option>
                    <option value="sold"     <?= $statusVal==='sold'?'selected':'' ?>>Vendu</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:9px 20px;"><i class="fas fa-search"></i> Filtrer</button>
            <?php if ($searchVal||$typeVal||$statusVal): ?>
            <a href="properties.php" class="btn btn-outline" style="padding:9px 16px;font-size:13px;"><i class="fas fa-times"></i> Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Table -->
<div class="table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-home" style="color:var(--primary);margin-right:8px;"></i>Liste des Biens</h3>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width:60px;">#</th><th>Titre</th><th>Type</th><th>Lieu</th><th>Prix</th>
                    <?php if (is_admin()): ?><th>Gestionnaire</th><?php endif; ?>
                    <th>Statut</th><th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['properties'] as $p): ?>
                <tr>
                    <td style="color:var(--gray-400);font-size:13px;"><?= (int)$p['id'] ?></td>
                    <td>
                        <a href="property-form.php?edit=<?= (int)$p['id'] ?>" style="color:var(--primary);font-weight:500;font-size:14px;"><?= htmlspecialchars($p['title'], ENT_QUOTES|ENT_HTML5) ?></a>
                        <?php if ($p['main_image']): ?><br><span style="font-size:11px;color:var(--gray-400);"><i class="fas fa-image"></i> Image</span><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($p['property_type']), ENT_QUOTES|ENT_HTML5) ?></td>
                    <td style="font-size:13px;"><?= htmlspecialchars($p['location'], ENT_QUOTES|ENT_HTML5) ?></td>
                    <td style="font-weight:600;color:var(--gold);white-space:nowrap;"><?= number_format((float)$p['price'],0,',',' ') ?> €</td>
                    <?php if (is_admin()): ?><td style="font-size:13px;"><?= htmlspecialchars($p['manager_name'], ENT_QUOTES|ENT_HTML5) ?></td><?php endif; ?>
                    <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <div class="action-btns">
                            <a href="property-form.php?edit=<?= (int)$p['id'] ?>" class="btn-icon edit" title="Modifier"><i class="fas fa-pencil-alt"></i></a>
                            <button class="btn-icon delete" title="Supprimer"
                                onclick="confirmAction('Supprimer « <?= htmlspecialchars($p['title'], ENT_QUOTES|ENT_JS_ESCAPE) ?> » ? Cette action est irréversible.',()=>{const f=document.createElement('form');f.method='POST';f.action='property-delete.php';f.innerHTML='<input type=&quot;hidden&quot; name=&quot;csrf_token&quot; value=&quot;<?= htmlspecialchars(csrf_generate(), ENT_QUOTES|ENT_HTML5) ?>&quot;><input type=&quot;hidden&quot; name=&quot;property_id&quot; value=&quot;<?= (int)$p['id'] ?>&quot;>';document.body.appendChild(f);f.submit();});">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($result['properties'])): ?>
                <tr><td colspan="<?= is_admin()?8:7 ?>" style="text-align:center;color:var(--gray-400);padding:40px;">
                    <i class="fas fa-home" style="font-size:24px;margin-bottom:8px;display:block;"></i>Aucun bien ne correspond à vos critères.
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($result['last_page'] > 1): ?>
<?php
$baseParams = http_build_query(array_filter(['type'=>$typeVal,'status'=>$statusVal,'search'=>$searchVal]));
$sep = $baseParams ? '&' : '';
?>
<div class="pagination-admin">
    <?php if ($page > 1): ?><a href="properties.php?<?= $baseParams ?><?= $sep ?>page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($i=1; $i<=$result['last_page']; $i++): ?>
    <a href="properties.php?<?= $baseParams ?><?= $sep ?>page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $result['last_page']): ?><a href="properties.php?<?= $baseParams ?><?= $sep ?>page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <div class="modal-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
        <h3>Confirmation</h3><p>Êtes-vous sûr ?</p>
        <div class="modal-actions">
            <button class="btn btn-outline btn-sm" id="modalCancelBtn">Annuler</button>
            <button class="btn btn-danger btn-sm" id="modalConfirmBtn">Confirmer</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
