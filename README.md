# H&M Immobilier — Site + Espace Admin

## Structure du projet

```
hm-immobilier/
├── admin/                      # Espace administration
│   ├── css/admin.css           # Styles admin
│   ├── js/admin.js             # JS admin (sidebar, modal, upload)
│   ├── includes/
│   │   ├── admin_header.php    # Header/sidebar partagé
│   │   └── admin_footer.php    # Footer partagé
│   ├── login.php               # Page connexion (rate-limited)
│   ├── logout.php              # Déconnexion
│   ├── dashboard.php           # Tableau de bord
│   ├── properties.php          # Liste des biens
│   ├── property-form.php       # Créer / Modifier un bien
│   ├── property-delete.php     # Suppression (POST only)
│   ├── users.php               # Liste des utilisateurs (admin)
│   ├── user-form.php           # Créer / Modifier utilisateur (admin)
│   ├── user-toggle.php         # Activer/Désactiver utilisateur
│   ├── audit.php               # Journal d'audit (admin)
│   └── profile.php             # Changement de mot de passe
├── api/
│   └── featured-properties.php # JSON pour l'accueil (3 biens)
├── includes/
│   ├── security.php            # CSRF, sessions, upload, rate-limit, audit
│   ├── UserModel.php           # Modèle utilisateur
│   └── PropertyModel.php       # Modèle bien immobilier
├── uploads/                    # Dossier d'upload (créé automatiquement)
│   └── properties/             # Images des biens
├── config.php                  # Configuration DB + constantes
├── database.sql                # Script SQL de création
├── index.html                  # Page d'accueil (front)
├── styles.css                  # Styles front (votre design original)
├── nos-biens.php               # Listing public avec filtres
└── bien.php                    # Page détail d'un bien
```

---

## Installation

### 1. Base de données

```bash
mysql -u root -p < database.sql
```

Puis, pour générer le hash du premier mot de passe admin, exécutez cette commande PHP :

```bash
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
```

Copiez le résultat et remplacez la valeur `'$2y$10$YourHashHereReplace'`  
dans `database.sql` à l'intérieur de l'INSERT du premier admin, puis réexécutez le script.

### 2. Configuration

Editez `config.php` et renseignez vos identifiants MySQL :

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hm_immobilier');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_mot_de_passe');
```

**Important en production :** utilisez des variables d'environnement plutôt que des valeurs en dur.

### 3. Hébergement

Déposez le dossier sur votre serveur PHP. Le fichier `config.php` est au niveau racine — il n'est pas accessible directement via le navigateur car il ne contient pas de balises HTML, mais en production, vous pouvez le déplacer **au-dessus** du dossier public pour plus de sécurité.

### 4. Première connexion

| Email | Mot de passe |
|---|---|
| `admin@hm-immobilier.fr` | `admin123` |

> **Changez immédiatement** ce mot de passe après la première connexion via **Mon Profil**.

---

## Sécurité — Résumé des mesures

| Mesure | Détail |
|---|---|
| **CSRF** | Chaque formulaire POST est protégé par un token HMAC-SHA256 signé et temporel (expiration 1h). |
| **Mots de passe** | Hashés avec `bcrypt` via `password_hash()`. Validation : 8+ car., majuscule, minuscule, chiffre. Limite à 72 car. (limite bcrypt). |
| **Requêtes SQL** | 100% via `PDO Prepared Statements`. Aucune concaténation de chaînes dans les requêtes. |
| **Session** | Régénération d'ID à chaque connexion. Vérification IP. Expiration après 1h. Cookie HttpOnly + SameSite=Strict. |
| **Rate Limiting** | 5 tentatives de connexion max en 5 minutes par IP. |
| **Upload** | Vérifie le MIME réel (`finfo`), l'extension (whitelist), la taille (5 Mo), et que c'est une image valide (`getimagesize`). Nom de fichier aléatoire (`random_bytes`). |
| **XSS** | Toutes les sorties sont échappées via `htmlspecialchars()` avec `ENT_QUOTES | ENT_HTML5`. |
| **Droits d'accès** | Un agent ne voit/modifie que ses propres biens. L'admin peut tout. Vérification à chaque action. |
| **Audit** | Chaque action sensible (login, création, suppression, échec) est enregistrée avec l'IP, l'utilisateur et un horodatage. |
| **Soft delete** | Les utilisateurs ne sont jamais supprimés (préserve l'historique). Ils sont désactivés. |

---

## Flux métier

### Création d'un utilisateur (admin)
1. L'admin va dans **Administration → Créer Utilisateur**
2. Il renseigne prénom, nom, email, mot de passe et **définit le rôle** (Agent ou Administrateur)
3. Le compte est créé immédiatement — l'utilisateur peut se connecter avec ces identifiants

### Gestion des biens
- Chaque bien a un **créateur** (`created_by`) et un **gestionnaire** (`managed_by`)
- À la création, `managed_by = created_by` par défaut
- L'**admin** peut réaffector un bien à un autre utilisateur via le champ « Gestionnaire »
- Un **agent** ne voit et ne peut modifier que les biens dont il est gestionnaire

### Réaffectation
Dans le formulaire d'édition d'un bien, seul l'admin voit le dropdown « Gestionnaire du Bien » permettant de changer l'assignation.
