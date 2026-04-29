<?php
/**
 * À inclure en haut de chaque page protégée.
 * Vérifie automatiquement la connexion ; le rôle requis est demandé via exiger_role().
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/fonctions-auth.php';
exiger_connexion();
