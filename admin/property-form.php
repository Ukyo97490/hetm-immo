<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/PropertyModel.php';
require_once __DIR__ . '/../includes/UserModel.php';

session_secure_start();
require_auth();

$model     = new PropertyModel();
$userModel = new UserModel();
$editId    = sanitize_int($_GET['edit'] ?? null);
$isEditing = ($editId !== null);
$property  = null;
$errors    = [];

if ($isEditing) {
    $property = $model->findById($editId);
    if ($property === null) { $_SESSION['flash_error']='Bien introuvable.'; header('Location: properties.php'); exit; }
    if (!is_admin() && (int)$property['managed_by'] !== (int)$_SESSION['user_id']) {
        $_SESSION['flash_error']='Vous n\'êtes pas autorisé à modifier ce bien.'; header('Location: properties.php'); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title       = sanitize_string($_POST['title'] ?? '', 255);
    $description = sanitize_string($_POST['description'] ?? '', 10000);
    $location    = sanitize_string($_POST['location'] ?? '', 255);
    $type        = $_POST['property_type'] ?? '';
    $price       = sanitize_float($_POST['price'] ?? null);
    $surface     = sanitize_float($_POST['surface'] ?? null);
    $bedrooms    = sanitize_int($_POST['bedrooms'] ?? null);
    $bathrooms   = sanitize_int($_POST['bathrooms'] ?? null);
    $hasPool     = isset($_POST['has_pool'])   ? 1 : 0;
    $hasGarage   = isset($_POST['has_garage']) ? 1 : 0;
    $hasGarden   = isset($_POST['has_garden']) ? 1 : 0;
    $status      = $_POST['status'] ?? 'active';
    $managedBy   = is_admin() ? sanitize_int($_POST['managed_by'] ?? null) : null;

    $validTypes   = ['maison','appartement','terrain','villa','chalet'];
    $validStatuts = ['active','inactive','sold'];

    if (empty($title))                              $errors[] = 'Le titre est obligatoire.';
    if (empty($location))                           $errors[] = 'La localisation est obligatoire.';
    if (!in_array($type, $validTypes, true))        $errors[] = 'Type de bien invalide.';
    if ($price === null || $price < 0)              $errors[] = 'Le prix doit être un nombre positif.';
    if (!in_array($status, $validStatuts, true))    $errors[] = 'Statut invalide.';
    if ($surface !== null && $surface < 0)          $errors[] = 'La surface doit être positive.';

    if (is_admin() && $managedBy !== null) {
        $targetUser = $userModel->findById($managedBy);
        if ($targetUser === null || !$targetUser['is_active']) { $errors[] = 'Utilisateur de gestion invalide ou inactif.'; $managedBy = null; }
    }

    $mainImage = $isEditing ? $property['main_image'] : null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK && $_FILES['main_image']['size'] > 0) {
        $uploaded = upload_image($_FILES['main_image'], 'properties');
        if ($uploaded === null) { $errors[] = 'Image invalide. Utilisez JPG, PNG, WebP ou GIF (max 5 Mo).'; }
        else {
            if ($isEditing && $property['main_image']) { $oldPath = UPLOAD_DIR . $property['main_image']; if (file_exists($oldPath)) unlink($oldPath); }
            $mainImage = $uploaded;
        }
    }

    if (empty($errors)) {
        $data = [
            'title'=>$title,'description'=>$description,'location'=>$location,'property_type'=>$type,
            'price'=>$price,'surface'=>$surface,'bedrooms'=>$bedrooms,'bathrooms'=>$bathrooms,
            'has_pool'=>$hasPool,'has_garage'=>$hasGarage,'has_garden'=>$hasGarden,'status'=>$status,'main_image'=>$mainImage,
        ];
        if ($managedBy !== null) $data['managed_by'] = $managedBy;

        if ($isEditing) {
            $model->update($editId, $data, $_SESSION['user_id'], is_admin());
            audit_log($_SESSION['user_id'], 'update_property', "property:$editId", "Bien modifié");
            $_SESSION['flash_success'] = 'Bien mis à jour avec succès.';
        } else {
            $newId = $model->create($data, $_SESSION['user_id']);
            audit_log($_SESSION['user_id'], 'create_property', "property:$newId", "Bien créé");
            $_SESSION['flash_success'] = 'Bien créé avec succès.';
        }
        header('Location: properties.php'); exit;
    }
}

$val = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $val = $_POST; }
elseif ($isEditing && $property) { $val = $property; }
else { $val = ['status' => 'active']; }

$agents = [];
if (is_admin()) {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, first_name, last_name, role FROM users WHERE is_active = 1 ORDER BY first_name");
    $stmt->execute();
    $agents = $stmt->fetchAll();
}

$pageTitle = $isEditing ? 'Modifier un Bien' : 'Ajouter un Bien';
include __DIR__ . '/includes/admin_header.php';
?>

<!-- Validation errors -->
<?php if (!empty($errors)): ?>
<div class="errors-block">
    <p><i class="fas fa-exclamation-circle"></i> Erreurs de validation</p>
    <ul><?php foreach($errors as $e): ?><li>• <?= htmlspecialchars($e, ENT_QUOTES|ENT_HTML5) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<!-- Form -->
<div class="form-card" style="max-width:860px;">
    <h2><?= $isEditing ? 'Modifier le Bien' : 'Créer un Nouveau Bien' ?></h2>
    <p class="form-subtitle"><?= $isEditing ? 'Modifiez les informations du bien ci-dessous.' : 'Remplissez les informations pour ajouter un nouveau bien.' ?></p>

    <form method="POST" action="property-form.php<?= $isEditing ? '?edit='.(int)$editId : '' ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group full">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($val['title']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="Ex: Villa Moderne à Saint-Tropez" required>
            </div>
            <div class="form-group">
                <label>Type de Bien <span class="req">*</span></label>
                <select name="property_type" required>
                    <option value="">— Choisir —</option>
                    <?php foreach (['maison','appartement','terrain','villa','chalet'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($val['property_type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Localisation <span class="req">*</span></label>
                <input type="text" name="location" value="<?= htmlspecialchars($val['location']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="Ex: Paris 16ème" required>
            </div>
            <div class="form-group">
                <label>Prix (€) <span class="req">*</span></label>
                <input type="text" name="price" value="<?= htmlspecialchars($val['price']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="Ex: 2800000" required>
            </div>
            <div class="form-group">
                <label>Surface (m²)</label>
                <input type="text" name="surface" value="<?= htmlspecialchars($val['surface']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="Ex: 300">
            </div>
            <div class="form-group">
                <label>Chambres</label>
                <input type="number" name="bedrooms" min="0" max="30" value="<?= htmlspecialchars($val['bedrooms']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="5">
            </div>
            <div class="form-group">
                <label>Salles de Bain</label>
                <input type="number" name="bathrooms" min="0" max="20" value="<?= htmlspecialchars($val['bathrooms']??'', ENT_QUOTES|ENT_HTML5) ?>" placeholder="3">
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="status">
                    <option value="active"   <?= ($val['status']??'')==='active'?'selected':'' ?>>Actif</option>
                    <option value="inactive" <?= ($val['status']??'')==='inactive'?'selected':'' ?>>Inactif</option>
                    <option value="sold"     <?= ($val['status']??'')==='sold'?'selected':'' ?>>Vendu</option>
                </select>
            </div>
            <div class="form-group full">
                <label>Commodités</label>
                <div class="checkbox-row">
                    <label class="checkbox-item"><input type="checkbox" name="has_pool"   <?= ($val['has_pool']??0)?'checked':'' ?>><span><i class="fas fa-swimming-pool" style="color:var(--info);"></i> Piscine</span></label>
                    <label class="checkbox-item"><input type="checkbox" name="has_garage" <?= ($val['has_garage']??0)?'checked':'' ?>><span><i class="fas fa-car" style="color:var(--gray-500);"></i> Garage</span></label>
                    <label class="checkbox-item"><input type="checkbox" name="has_garden" <?= ($val['has_garden']??0)?'checked':'' ?>><span><i class="fas fa-leaf" style="color:var(--success);"></i> Jardin</span></label>
                </div>
            </div>
            <div class="form-group full">
                <label>Description</label>
                <textarea name="description" placeholder="Décrivez le bien en détail..."><?= htmlspecialchars($val['description']??'', ENT_QUOTES|ENT_HTML5) ?></textarea>
            </div>
            <div class="form-group full">
                <label>Image Principale</label>
                <div class="upload-zone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Cliquez pour télécharger une image</p>
                    <p class="upload-hint">JPG, PNG, WebP ou GIF — max 5 Mo</p>
                    <input type="file" name="main_image" accept="image/*">
                </div>
                <?php if ($isEditing && $property['main_image']): ?>
                <img src="../uploads/<?= htmlspecialchars($property['main_image'], ENT_QUOTES|ENT_HTML5) ?>" alt="Image actuelle" class="image-preview" style="display:block;margin-top:12px;">
                <p style="font-size:11px;color:var(--gray-400);margin-top:6px;"><i class="fas fa-info-circle"></i> Téléchargez une nouvelle image pour remplacer l'actuelle.</p>
                <?php endif; ?>
            </div>
            <?php if (is_admin()): ?>
            <div class="form-group">
                <label>Gestionnaire du Bien</label>
                <select name="managed_by">
                    <option value="">— Choisir —</option>
                    <?php foreach ($agents as $agent): ?>
                    <option value="<?= (int)$agent['id'] ?>" <?= ($val['managed_by']??'')==$agent['id']?'selected':'' ?>>
                        <?= htmlspecialchars($agent['first_name'].' '.$agent['last_name'], ENT_QUOTES|ENT_HTML5) ?>
                        <?php if ($agent['role']==='admin'): ?><span style="color:var(--gold);font-size:11px;">(Admin)</span><?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="hint">Vous pouvez réaffector la gestion de ce bien à un autre utilisateur.</p>
            </div>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <a href="properties.php" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-gold"><i class="fas fa-<?= $isEditing?'save':'plus' ?>"></i> <?= $isEditing?'Mettre à jour':'Créer le Bien' ?></button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
