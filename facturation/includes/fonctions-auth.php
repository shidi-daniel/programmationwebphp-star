<?php
/**
 * Fonctions d'authentification et de contrôle d'accès (RBAC)
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Charge tous les utilisateurs depuis le fichier JSON.
 */
function charger_utilisateurs(): array {
    if (!file_exists(FICHIER_UTILISATEURS)) {
        return [];
    }
    $contenu = file_get_contents(FICHIER_UTILISATEURS);
    $data = json_decode($contenu, true);
    return is_array($data) ? $data : [];
}

/**
 * Sauvegarde la liste complète des utilisateurs.
 */
function sauvegarder_utilisateurs(array $utilisateurs): bool {
    $json = json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(FICHIER_UTILISATEURS, $json, LOCK_EX) !== false;
}

/**
 * Recherche un utilisateur par identifiant.
 */
function trouver_utilisateur(string $identifiant): ?array {
    foreach (charger_utilisateurs() as $u) {
        if ($u['identifiant'] === $identifiant) {
            return $u;
        }
    }
    return null;
}

/**
 * Tente de connecter un utilisateur.
 */
function connecter_utilisateur(string $identifiant, string $mot_de_passe): bool {
    $u = trouver_utilisateur($identifiant);
    if ($u === null || empty($u['actif'])) {
        return false;
    }
    if (!password_verify($mot_de_passe, $u['mot_de_passe'])) {
        return false;
    }
    demarrer_session_securisee();
    session_regenerate_id(true);
    $_SESSION['utilisateur'] = [
        'identifiant'  => $u['identifiant'],
        'role'         => $u['role'],
        'nom_complet'  => $u['nom_complet'],
    ];
    $_SESSION['derniere_activite'] = time();
    return true;
}

/**
 * Déconnecte l'utilisateur courant.
 */
function deconnecter_utilisateur(): void {
    demarrer_session_securisee();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Crée un nouveau compte utilisateur.
 */
function creer_compte(string $identifiant, string $mot_de_passe, string $role, string $nom_complet): array {
    $erreurs = [];
    if (!preg_match('/^[a-z0-9._-]{3,30}$/i', $identifiant)) {
        $erreurs[] = "Identifiant invalide (3-30 caractères alphanumériques).";
    }
    if (strlen($mot_de_passe) < 6) {
        $erreurs[] = "Le mot de passe doit faire au moins 6 caractères.";
    }
    if (!in_array($role, [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER], true)) {
        $erreurs[] = "Rôle inconnu.";
    }
    if (trim($nom_complet) === '') {
        $erreurs[] = "Le nom complet est obligatoire.";
    }
    if (trouver_utilisateur($identifiant) !== null) {
        $erreurs[] = "Cet identifiant est déjà utilisé.";
    }
    if (!empty($erreurs)) {
        return ['ok' => false, 'erreurs' => $erreurs];
    }

    $utilisateurs = charger_utilisateurs();
    $utilisateurs[] = [
        'identifiant'    => $identifiant,
        'mot_de_passe'   => password_hash($mot_de_passe, PASSWORD_DEFAULT),
        'role'           => $role,
        'nom_complet'    => $nom_complet,
        'date_creation'  => date('Y-m-d'),
        'actif'          => true,
    ];
    sauvegarder_utilisateurs($utilisateurs);
    return ['ok' => true, 'erreurs' => []];
}

/**
 * Supprime un compte utilisateur (sauf le super admin).
 */
function supprimer_compte(string $identifiant): bool {
    $utilisateurs = charger_utilisateurs();
    $nouveaux = [];
    foreach ($utilisateurs as $u) {
        if ($u['identifiant'] === $identifiant && $u['role'] === ROLE_SUPER) {
            $nouveaux[] = $u; // on protège le super admin
            continue;
        }
        if ($u['identifiant'] !== $identifiant) {
            $nouveaux[] = $u;
        }
    }
    return sauvegarder_utilisateurs($nouveaux);
}

/**
 * Vérifie qu'un utilisateur est connecté, sinon redirige vers login.
 */
function exiger_connexion(): void {
    demarrer_session_securisee();

    if (isset($_SESSION['derniere_activite']) &&
        (time() - $_SESSION['derniere_activite'] > SESSION_TIMEOUT)) {
        deconnecter_utilisateur();
        header('Location: /facturation/auth/login.php?timeout=1');
        exit;
    }
    if (!isset($_SESSION['utilisateur'])) {
        header('Location: /facturation/auth/login.php');
        exit;
    }
    $_SESSION['derniere_activite'] = time();
}

/**
 * Vérifie que l'utilisateur a au moins un des rôles requis.
 */
function exiger_role(array $roles_autorises): void {
    exiger_connexion();
    $role = $_SESSION['utilisateur']['role'] ?? '';
    if (!in_array($role, $roles_autorises, true)) {
        http_response_code(403);
        echo "<h1>403 - Accès interdit</h1>";
        echo "<p>Votre rôle (<b>" . htmlspecialchars($role) . "</b>) n'autorise pas cette action.</p>";
        echo '<p><a href="/facturation/index.php">Retour à l\'accueil</a></p>';
        exit;
    }
}

function utilisateur_connecte(): ?array {
    demarrer_session_securisee();
    return $_SESSION['utilisateur'] ?? null;
}
