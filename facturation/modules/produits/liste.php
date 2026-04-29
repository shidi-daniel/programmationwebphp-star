<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';
exiger_role([ROLE_MANAGER, ROLE_SUPER]);

$produits = charger_produits();
$titre_page = "Catalogue produits";
include __DIR__ . '/../../includes/header.php';
?>
<h1>Catalogue des produits (<?= count($produits) ?>)</h1>
<p><a href="/facturation/modules/produits/enregistrer.php" class="btn-primary">+ Nouveau produit</a></p>

<?php if (empty($produits)): ?>
    <p class="muted">Aucun produit enregistré.</p>
<?php else: ?>
<table class="data-table">
    <thead>
    <tr>
        <th>Code-barres</th><th>Nom</th><th>Prix HT</th>
        <th>Stock</th><th>Expiration</th><th>Enregistré le</th><th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($produits as $p): ?>
        <tr>
            <td><code><?= htmlspecialchars($p['code_barre']) ?></code></td>
            <td><?= htmlspecialchars($p['nom']) ?></td>
            <td><?= number_format($p['prix_unitaire_ht'], 0, ',', ' ') ?> <?= DEVISE ?></td>
            <td class="<?= $p['quantite_stock'] < 5 ? 'stock-bas' : '' ?>">
                <?= (int)$p['quantite_stock'] ?>
            </td>
            <td><?= htmlspecialchars($p['date_expiration']) ?></td>
            <td><?= htmlspecialchars($p['date_enregistrement'] ?? '-') ?></td>
            <td><a href="/facturation/modules/produits/enregistrer.php?code=<?= urlencode($p['code_barre']) ?>">Modifier</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
