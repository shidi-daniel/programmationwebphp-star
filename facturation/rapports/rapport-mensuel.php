<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../includes/fonctions-factures.php';
exiger_role([ROLE_MANAGER, ROLE_SUPER]);

$mois = $_GET['mois'] ?? date('Y-m');
$factures = array_values(array_filter(charger_factures(), fn($f) => str_starts_with($f['date'], $mois)));
$total_ttc = array_sum(array_column($factures, 'total_ttc'));
$total_ht  = array_sum(array_column($factures, 'total_ht'));
$total_tva = array_sum(array_column($factures, 'tva'));

// Agrégation par jour
$par_jour = [];
foreach ($factures as $f) {
    $par_jour[$f['date']] = ($par_jour[$f['date']] ?? 0) + $f['total_ttc'];
}
ksort($par_jour);

$titre_page = "Rapport mensuel";
include __DIR__ . '/../includes/header.php';
?>
<h1>📈 Rapport mensuel</h1>
<form method="get" class="form-inline">
    <label>Mois <input type="month" name="mois" value="<?= htmlspecialchars($mois) ?>"></label>
    <button class="btn-primary" type="submit">Afficher</button>
</form>

<div class="kpis">
    <div class="kpi"><span>Factures</span><b><?= count($factures) ?></b></div>
    <div class="kpi"><span>Total HT</span><b><?= formater_montant($total_ht) ?></b></div>
    <div class="kpi"><span>TVA</span><b><?= formater_montant($total_tva) ?></b></div>
    <div class="kpi"><span>Total TTC</span><b><?= formater_montant($total_ttc) ?></b></div>
</div>

<h2>Détail par jour</h2>
<table class="data-table">
    <thead><tr><th>Date</th><th>Total TTC</th></tr></thead>
    <tbody>
    <?php foreach ($par_jour as $d => $t): ?>
        <tr><td><?= htmlspecialchars($d) ?></td><td><?= formater_montant($t) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
