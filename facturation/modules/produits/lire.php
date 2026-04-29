<?php
/**
 * Endpoint AJAX : retourne les infos d'un produit JSON par code-barres.
 */
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

header('Content-Type: application/json; charset=utf-8');
$code = $_GET['code'] ?? '';
$produit = trouver_produit($code);
if ($produit) {
    echo json_encode(['ok' => true, 'produit' => $produit]);
} else {
    echo json_encode(['ok' => false, 'message' => 'Produit inconnu']);
}
