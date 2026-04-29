<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';
exiger_role([ROLE_MANAGER, ROLE_SUPER]);

$erreurs = [];
$succes  = '';
$donnees = [
    'code_barre' => $_GET['code'] ?? ($_POST['code_barre'] ?? ''),
    'nom' => $_POST['nom'] ?? '',
    'prix_unitaire_ht' => $_POST['prix_unitaire_ht'] ?? '',
    'date_expiration' => $_POST['date_expiration'] ?? '',
    'quantite_stock' => $_POST['quantite_stock'] ?? '',
];

// Si on a un code-barres, vérifier s'il existe déjà
$existant = null;
if ($donnees['code_barre'] !== '') {
    $existant = trouver_produit($donnees['code_barre']);
    if ($existant && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $donnees['nom']              = $existant['nom'];
        $donnees['prix_unitaire_ht'] = $existant['prix_unitaire_ht'];
        $donnees['date_expiration']  = $existant['date_expiration'];
        $donnees['quantite_stock']   = $existant['quantite_stock'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = enregistrer_produit($donnees);
    if ($res['ok']) {
        $succes = $res['mis_a_jour']
            ? "Produit mis à jour avec succès."
            : "Nouveau produit enregistré avec succès.";
    } else {
        $erreurs = $res['erreurs'];
    }
}

$titre_page = "Enregistrer un produit";
include __DIR__ . '/../../includes/header.php';
?>
<h1>Enregistrement / Mise à jour d'un produit</h1>

<section class="scanner-block">
    <h2>1. Scanner le code-barres</h2>
    <button id="btn-scanner" class="btn-primary" type="button">📷 Activer la caméra</button>
    <button id="btn-stop" class="btn-secondary" type="button" style="display:none">⏹ Arrêter</button>
    <div id="scanner-container" style="display:none">
        <video id="video-scanner" playsinline></video>
    </div>
    <p id="scanner-status" class="muted"></p>
</section>

<section>
    <h2>2. Informations du produit</h2>
    <?php if ($succes): ?><div class="alert-success"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($erreurs): ?>
        <div class="alert-error">
            <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($existant && !$succes): ?>
        <div class="alert-info">⚠️ Ce code-barres est déjà référencé. La soumission mettra à jour le produit.</div>
    <?php endif; ?>

    <form method="post" class="form-produit">
        <label>Code-barres
            <input type="text" name="code_barre" id="champ-code"
                   value="<?= htmlspecialchars($donnees['code_barre']) ?>" required>
        </label>
        <label>Nom du produit
            <input type="text" name="nom" value="<?= htmlspecialchars($donnees['nom']) ?>" required maxlength="100">
        </label>
        <label>Prix unitaire HT (<?= DEVISE ?>)
            <input type="number" step="0.01" min="0" name="prix_unitaire_ht"
                   value="<?= htmlspecialchars((string)$donnees['prix_unitaire_ht']) ?>" required>
        </label>
        <label>Date d'expiration
            <input type="date" name="date_expiration"
                   value="<?= htmlspecialchars($donnees['date_expiration']) ?>" required>
        </label>
        <label>Quantité initiale en stock
            <input type="number" min="0" step="1" name="quantite_stock"
                   value="<?= htmlspecialchars((string)$donnees['quantite_stock']) ?>" required>
        </label>
        <button type="submit" class="btn-primary">💾 Enregistrer</button>
    </form>
</section>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.0/umd/index.min.js"></script>
<script src="/facturation/assets/js/scanner.js"></script>
<script>
  initScanner({
    btnStart: 'btn-scanner',
    btnStop:  'btn-stop',
    container:'scanner-container',
    video:    'video-scanner',
    status:   'scanner-status',
    onDetect: function(code) {
      document.getElementById('champ-code').value = code;
    }
  });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
