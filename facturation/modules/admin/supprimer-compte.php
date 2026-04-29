<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
exiger_role([ROLE_SUPER]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['identifiant'] ?? '';
    if ($id !== '') {
        supprimer_compte($id);
    }
}
header('Location: /facturation/modules/admin/gestion-comptes.php');
exit;
