<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions-auth.php';
$user = utilisateur_connecte();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre_page ?? 'Système de Facturation') ?> - <?= NOM_COMMERCE ?></title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">
        <a href="/facturation/index.php"><?= NOM_COMMERCE ?></a>
    </div>
    <button class="hamburger" aria-label="Menu">☰</button>
    <?php if ($user): ?>
    <nav class="menu nav-menu">
        <a href="/facturation/modules/facturation/nouvelle-facture.php">Nouvelle facture</a>
        <?php if (in_array($user['role'], [ROLE_MANAGER, ROLE_SUPER], true)): ?>
            <a href="/facturation/modules/produits/liste.php">Produits</a>
            <a href="/facturation/modules/produits/enregistrer.php">+ Produit</a>
            <a href="/facturation/rapports/rapport-journalier.php">Rapport J</a>
            <a href="/facturation/rapports/rapport-mensuel.php">Rapport M</a>
        <?php endif; ?>
        <?php if ($user['role'] === ROLE_SUPER): ?>
            <a href="/facturation/modules/admin/gestion-comptes.php">Comptes</a>
        <?php endif; ?>
    </nav>
    <div class="user-box">
        <span><?= htmlspecialchars($user['nom_complet']) ?> (<i><?= htmlspecialchars($user['role']) ?></i>)</span>
        <a class="btn-logout" href="/facturation/auth/logout.php">Déconnexion</a>
    </div>
    <?php endif; ?>
</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
});
</script>
<main class="container">
