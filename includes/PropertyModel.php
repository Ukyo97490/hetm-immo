<?php
/**
 * H&M Immobilier — Modèle Property
 */

require_once __DIR__ . '/../config.php';

class PropertyModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // ── Recherche ────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    CONCAT(uc.first_name, " ", uc.last_name) as creator_name,
                    CONCAT(um.first_name, " ", um.last_name) as manager_name
             FROM properties p
             LEFT JOIN users uc ON p.created_by = uc.id
             LEFT JOIN users um ON p.managed_by = um.id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Liste paginée avec filtres optionnels.
     * $filters : ['type', 'status', 'search', 'managed_by']
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['type'])) {
            $conditions[] = 'p.property_type = ?';
            $params[]     = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'p.status = ?';
            $params[]     = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = '(p.title LIKE ? OR p.location LIKE ?)';
            $search       = '%' . sanitize_string($filters['search'], 100) . '%';
            $params[]     = $search;
            $params[]     = $search;
        }
        if (!empty($filters['managed_by'])) {
            $conditions[] = 'p.managed_by = ?';
            $params[]     = (int)$filters['managed_by'];
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Count total
        $countSql  = "SELECT COUNT(*) as cnt FROM properties p $where";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['cnt'];

        // Données paginées
        $offset = max(0, ($page - 1) * $perPage);
        $sql    = "SELECT p.*,
                          CONCAT(uc.first_name, ' ', uc.last_name) as creator_name,
                          CONCAT(um.first_name, ' ', um.last_name) as manager_name
                   FROM properties p
                   LEFT JOIN users uc ON p.created_by = uc.id
                   LEFT JOIN users um ON p.managed_by = um.id
                   $where
                   ORDER BY p.created_at DESC
                   LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'properties' => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => max(1, (int)ceil($total / $perPage)),
        ];
    }

    /**
     * Biens gérés par un utilisateur spécifique.
     */
    public function findByManager(int $userId, int $page = 1, int $perPage = 12): array
    {
        return $this->findAll(['managed_by' => $userId], $page, $perPage);
    }

    // ── Création ─────────────────────────────────────────

    /**
     * Crée un bien. managed_by = created_by par défaut.
     */
    public function create(array $data, int $createdBy): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO properties (created_by, managed_by, title, description, location,
             property_type, price, surface, bedrooms, bathrooms, has_pool, has_garage, has_garden, main_image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $createdBy,
            $data['managed_by']      ?? $createdBy,
            $data['title']           ?? '',
            $data['description']     ?? '',
            $data['location']        ?? '',
            $data['property_type']   ?? 'maison',
            $data['price']           ?? 0,
            $data['surface']         ?? null,
            $data['bedrooms']        ?? null,
            $data['bathrooms']       ?? null,
            $data['has_pool']        ?? 0,
            $data['has_garage']      ?? 0,
            $data['has_garden']      ?? 0,
            $data['main_image']      ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ── Mise à jour ──────────────────────────────────────

    /**
     * Met à jour un bien.
     * Un agent ne peut modifier que ses propres biens (managed_by).
     * Un admin peut tout modifier.
     */
    public function update(int $id, array $data, int $userId, bool $isAdmin): bool
    {
        // Vérifie les droits d'accès
        if (!$isAdmin && !$this->canManage($id, $userId)) {
            return false;
        }

        $sets   = [];
        $params = [];

        $allowed = [
            'title', 'description', 'location', 'property_type',
            'price', 'surface', 'bedrooms', 'bathrooms',
            'has_pool', 'has_garage', 'has_garden', 'status', 'main_image'
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "$field = ?";
                $params[] = $data[$field];
            }
        }

        // L'admin peut réaffector le gestionnaire
        if ($isAdmin && isset($data['managed_by'])) {
            $sets[]   = 'managed_by = ?';
            $params[] = (int)$data['managed_by'];
        }

        if (empty($sets)) return false;

        $params[] = $id;
        $sql      = 'UPDATE properties SET ' . implode(', ', $sets) . ' WHERE id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // ── Suppression ──────────────────────────────────────

    /**
     * Supprime un bien (avec vérification de droits).
     */
    public function delete(int $id, int $userId, bool $isAdmin): bool
    {
        if (!$isAdmin && !$this->canManage($id, $userId)) {
            return false;
        }

        // Supprime d'abord les images associées
        $this->deleteImages($id);

        $stmt = $this->db->prepare('DELETE FROM properties WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // ── Images ───────────────────────────────────────────

    public function addImage(int $propertyId, string $filePath, int $order = 0): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO property_images (property_id, file_path, sort_order) VALUES (?, ?, ?)'
        );
        $stmt->execute([$propertyId, $filePath, $order]);
        return (int)$this->db->lastInsertId();
    }

    public function getImages(int $propertyId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order ASC'
        );
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll();
    }

    public function deleteImages(int $propertyId): void
    {
        // Récupère les chemins pour supprimer les fichiers
        $images = $this->getImages($propertyId);
        foreach ($images as $img) {
            $path = UPLOAD_DIR . $img['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->db->prepare('DELETE FROM property_images WHERE property_id = ?')
                 ->execute([$propertyId]);
    }

    public function deleteImage(int $imageId): bool
    {
        $stmt = $this->db->prepare('SELECT * FROM property_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $img  = $stmt->fetch();

        if ($img) {
            $path = UPLOAD_DIR . $img['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
            $this->db->prepare('DELETE FROM property_images WHERE id = ?')
                     ->execute([$imageId]);
            return true;
        }
        return false;
    }

    // ── Contrôle d'accès ─────────────────────────────────

    /**
     * Vérifie si un utilisateur peut gérer ce bien.
     */
    public function canManage(int $propertyId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM properties WHERE id = ? AND managed_by = ? LIMIT 1'
        );
        $stmt->execute([$propertyId, $userId]);
        return $stmt->fetch() !== false;
    }

    // ── Biens publics (pour le front) ────────────────────

    public function findPublicAll(array $filters = [], int $page = 1, int $perPage = 6): array
    {
        $filters['status'] = 'active';
        return $this->findAll($filters, $page, $perPage);
    }
}
