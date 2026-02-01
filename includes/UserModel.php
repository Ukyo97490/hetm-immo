<?php
/**
 * H&M Immobilier — Modèle User
 * Toutes les requêtes utilisent des prepared statements (PDO).
 */

require_once __DIR__ . '/../config.php';

class UserModel
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
            'SELECT id, email, first_name, last_name, role, is_active, created_at
             FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, email, password_hash, first_name, last_name, role, is_active
             FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([strtolower($email)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Liste paginée des utilisateurs (admin only).
     */
    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        // Count total
        $total = $this->db->query('SELECT COUNT(*) as cnt FROM users')->fetch()['cnt'];

        // Données paginées
        $stmt = $this->db->prepare(
            'SELECT id, email, first_name, last_name, role, is_active, created_at
             FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$perPage, $offset]);

        return [
            'users'      => $stmt->fetchAll(),
            'total'      => (int)$total,
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => max(1, (int)ceil($total / $perPage)),
        ];
    }

    // ── Création ─────────────────────────────────────────

    /**
     * Vérifie si l'email existe déjà.
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower($email)]);
        return $stmt->fetch() !== false;
    }

    /**
     * Crée un nouvel utilisateur.
     * Le mot de passe est hashé avec bcrypt (PASSWORD_BCRYPT).
     */
    public function create(string $email, string $password, string $firstName, string $lastName, string $role = 'agent'): int|false
    {
        $email = strtolower($email);

        if ($this->emailExists($email)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Whitelist du rôle
        $role = in_array($role, ['admin', 'agent'], true) ? $role : 'agent';

        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$email, $hash, $firstName, $lastName, $role]);

        return (int)$this->db->lastInsertId();
    }

    // ── Authentification ─────────────────────────────────

    /**
     * Vérifie les identifiants.
     * Retourne l'utilisateur si OK, null sinon.
     * Utilise password_verify() pour une comparaison sécurisée.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null || !$user['is_active']) {
            // Exécute password_verify quand même pour éviter le timing attack
            password_verify($password, '$2y$10$invalidhashtopreventtimingattack000000000000000000000');
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Si le hash est obsolète, le renouvelle
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                     ->execute([$newHash, $user['id']]);
        }

        return $user;
    }

    // ── Mise à jour ──────────────────────────────────────

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];

        // Whiteliste des champs autorisés à modifier
        $allowed = ['first_name', 'last_name', 'role', 'is_active'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $sets[]   = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($sets)) return false;

        $params[] = $id;
        $sql      = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // ── Suppression ──────────────────────────────────────

    /**
     * Désactive un utilisateur (soft delete).
     * On ne supprime jamais pour préserver l'historique audit.
     */
    public function deactivate(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function activate(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = 1 WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
