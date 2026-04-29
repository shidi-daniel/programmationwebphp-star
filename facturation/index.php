<?php
require_once __DIR__ . '/auth/session.php';
$titre_page = "Accueil";
include __DIR__ . '/includes/header.php';
$user = utilisateur_connecte();
?>
<h1>Bienvenue, <?= htmlspecialchars($user['nom_complet']) ?></h1>
<p class="role-info">Vous êtes connecté en tant que <strong><?= htmlspecialchars($user['role']) ?></strong>.</p>

<div class="cards">
    <a class="card" href="/facturation/modules/facturation/nouvelle-facture.php">
        <h3>🧾 Nouvelle facture</h3>
        <p>Scanner les codes-barres et encaisser une vente.</p>
    </a>

    <?php if (in_array($user['role'], [ROLE_MANAGER, ROLE_SUPER], true)): ?>
    <a class="card" href="/facturation/modules/produits/enregistrer.php">
        <h3>📦 Enregistrer un produit</h3>
        <p>Associer un code-barres à un nouveau produit.</p>
    </a>
    <a class="card" href="/facturation/modules/produits/liste.php">
        <h3>📋 Catalogue produits</h3>
        <p>Consulter et mettre à jour le stock.</p>
    </a>
    <a class="card" href="/facturation/rapports/rapport-journalier.php">
        <h3>📊 Rapport journalier</h3>
        <p>Synthèse des ventes du jour.</p>
    </a>
    <a class="card" href="/facturation/rapports/rapport-mensuel.php">
        <h3>📈 Rapport mensuel</h3>
        <p>Synthèse des ventes du mois.</p>
    </a>
    <?php endif; ?>

    <?php if ($user['role'] === ROLE_SUPER): ?>
    <a class="card" href="/facturation/modules/admin/gestion-comptes.php">
        <h3>👥 Gestion des comptes</h3>
        <p>Créer et supprimer les comptes Caissiers et Managers.</p>
    </a>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
