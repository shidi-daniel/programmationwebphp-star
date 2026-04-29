<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';
exiger_role([ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER]);

$id = $_GET['id'] ?? '';
$facture = trouver_facture($id);
$titre_page = "Facture " . $id;
include __DIR__ . '/../../includes/header.php';

if (!$facture) {
    echo "<p class='alert-error'>Facture introuvable.</p>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>
<div class="facture-print">
    <header class="facture-head">
        <h1><?= NOM_COMMERCE ?></h1>
        <p>Facture <b><?= htmlspecialchars($facture['id_facture']) ?></b></p>
        <p>Date : <?= htmlspecialchars($facture['date']) ?> à <?= htmlspecialchars($facture['heure']) ?></p>
        <p>Caissier : <?= htmlspecialchars($facture['caissier']) ?></p>
    </header>

    <table class="data-table">
        <thead>
        <tr><th>Désignation</th><th>Prix unit. HT</th><th>Qté</th><th>Sous-total HT</th></tr>
        </thead>
        <tbody>
        <?php foreach ($facture['articles'] as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['nom']) ?></td>
            <td><?= formater_montant($a['prix_unitaire_ht']) ?></td>
            <td><?= $a['quantite'] ?></td>
            <td><?= formater_montant($a['sous_total_ht']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="3" class="ta-right"><b>Total HT</b></td><td><?= formater_montant($facture['total_ht']) ?></td></tr>
        <tr><td colspan="3" class="ta-right"><b>TVA (<?= TVA_TAUX*100 ?>%)</b></td><td><?= formater_montant($facture['tva']) ?></td></tr>
        <tr><td colspan="3" class="ta-right"><b>Net à payer</b></td><td><b><?= formater_montant($facture['total_ttc']) ?></b></td></tr>
        </tbody>
    </table>

    <p class="merci">Merci de votre achat !</p>
</div>

<div class="actions no-print">
    <button onclick="window.print()" class="btn-primary">🖨 Imprimer</button>
    <a class="btn-secondary" href="/facturation/modules/facturation/nouvelle-facture.php">Nouvelle facture</a>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
