<?php
/**
 * Endpoint AJAX : calcule les totaux d'un panier envoyé en JSON.
 * Utilitaire complémentaire (le calcul principal est côté nouvelle-facture.php).
 */
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['ok' => false, 'message' => 'Données invalides']);
    exit;
}
echo json_encode(['ok' => true, 'totaux' => calculer_totaux($input)]);
