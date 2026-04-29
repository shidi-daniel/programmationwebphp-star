<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../includes/fonctions-factures.php';
exiger_role([ROLE_MANAGER, ROLE_SUPER]);

$jour = $_GET['jour'] ?? date('Y-m-d');
$factures = array_values(array_filter(charger_factures(), fn($f) => $f['date'] === $jour));
$total_ttc = array_sum(array_column($factures, 'total_ttc'));
$total_ht  = array_sum(array_column($factures, 'total_ht'));
$total_tva = array_sum(array_column($factures, 'tva'));

$titre_page = "Rapport journalier";
include __DIR__ . '/../includes/header.php';
?>
<h1>📊 Rapport journalier</h1>
<form method="get" class="form-inline">
    <label>Jour <input type="date" name="jour" value="<?= htmlspecialchars($jour) ?>"></label>
    <button class="btn-primary" type="submit">Afficher</button>
</form>

<div class="kpis">
    <div class="kpi"><span>Factures</span><b><?= count($factures) ?></b></div>
    <div class="kpi"><span>Total HT</span><b><?= formater_montant($total_ht) ?></b></div>
    <div class="kpi"><span>TVA</span><b><?= formater_montant($total_tva) ?></b></div>
    <div class="kpi"><span>Total TTC</span><b><?= formater_montant($total_ttc) ?></b></div>
</div>

<table class="data-table">
    <thead><tr><th>ID</th><th>Heure</th><th>Caissier</th><th>Articles</th><th>Total TTC</th></tr></thead>
    <tbody>
    <?php foreach ($factures as $f): ?>
        <tr>
            <td><a href="/facturation/modules/facturation/afficher-facture.php?id=<?= urlencode($f['id_facture']) ?>"><?= htmlspecialchars($f['id_facture']) ?></a></td>
            <td><?= htmlspecialchars($f['heure']) ?></td>
            <td><?= htmlspecialchars($f['caissier']) ?></td>
            <td><?= count($f['articles']) ?></td>
            <td><?= formater_montant($f['total_ttc']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
