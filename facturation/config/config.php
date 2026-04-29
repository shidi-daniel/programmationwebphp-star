<?php
/**
 * Configuration globale de l'application de facturation
 * UPC - Faculté de Sciences Informatiques - L2 FASI 2025-2026
 */

// Empêche tout accès direct sans inclusion
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// --- Paramètres commerciaux ---
define('TVA_TAUX', 0.18);                // TVA à 18%
define('DEVISE', 'CDF');                 // Franc Congolais
define('NOM_COMMERCE', 'Super Marché UPC');

// --- Chemins des fichiers de persistance ---
define('FICHIER_PRODUITS',     APP_ROOT . '/data/produits.json');
define('FICHIER_FACTURES',     APP_ROOT . '/data/factures.json');
define('FICHIER_UTILISATEURS', APP_ROOT . '/data/utilisateurs.json');

// --- Sécurité session ---
define('SESSION_TIMEOUT', 1800); // 30 minutes d'inactivité

// --- Rôles disponibles ---
define('ROLE_CAISSIER', 'caissier');
define('ROLE_MANAGER',  'manager');
define('ROLE_SUPER',    'super_admin');

// Démarrage de session sécurisé (à appeler partout via auth/session.php)
function demarrer_session_securisee(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}
