<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';
// Tous les rôles peuvent créer une facture
exiger_role([ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER]);

demarrer_session_securisee();
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        $code = trim($_POST['code_barre'] ?? '');
        $qte  = (int)($_POST['quantite'] ?? 0);
        $produit = trouver_produit($code);
        if (!$produit) {
            $erreurs[] = "Produit inconnu : $code. Demandez au Manager de l'enregistrer.";
        } elseif ($qte <= 0) {
            $erreurs[] = "Quantité invalide.";
        } elseif ($produit['quantite_stock'] < $qte) {
            $erreurs[] = "Stock insuffisant ({$produit['quantite_stock']} disponible).";
        } else {
            // Cumul si déjà présent
            $trouve = false;
            foreach ($_SESSION['panier'] as &$ligne) {
                if ($ligne['code_barre'] === $code) {
                    if ($produit['quantite_stock'] < $ligne['quantite'] + $qte) {
                        $erreurs[] = "Stock insuffisant après cumul.";
                    } else {
                        $ligne['quantite'] += $qte;
                    }
                    $trouve = true;
                    break;
                }
            }
            unset($ligne);
            if (!$trouve) {
                $_SESSION['panier'][] = [
                    'code_barre' => $produit['code_barre'],
                    'nom' => $produit['nom'],
                    'prix_unitaire_ht' => $produit['prix_unitaire_ht'],
                    'quantite' => $qte,
                ];
            }
            if (empty($erreurs)) {
                $message = "Article ajouté.";
            }
        }
    } elseif ($action === 'retirer') {
        $idx = (int)($_POST['index'] ?? -1);
        if (isset($_SESSION['panier'][$idx])) {
            array_splice($_SESSION['panier'], $idx, 1);
            $message = "Article retiré.";
        }
    } elseif ($action === 'vider') {
        $_SESSION['panier'] = [];
        $message = "Panier vidé.";
    } elseif ($action === 'valider') {
        $user = utilisateur_connecte();
        $res = enregistrer_facture($_SESSION['panier'], $user['identifiant']);
        if ($res['ok']) {
            $_SESSION['panier'] = [];
            header('Location: /facturation/modules/facturation/afficher-facture.php?id=' . urlencode($res['facture']['id_facture']));
            exit;
        }
        $erreurs = $res['erreurs'];
    }
}

$totaux = calculer_totaux($_SESSION['panier']);
$titre_page = "Nouvelle facture";
include __DIR__ . '/../../includes/header.php';
?>
<h1>🧾 Nouvelle facture</h1>

<?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($erreurs): ?>
    <div class="alert-error">
        <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<section class="scanner-block">
    <h2>Scanner ou saisir un code-barres</h2>
    <button id="btn-scanner" class="btn-primary" type="button">📷 Activer la caméra</button>
    <button id="btn-stop" class="btn-secondary" type="button" style="display:none">⏹ Arrêter</button>
    <div id="scanner-container" style="display:none">
        <video id="video-scanner" playsinline></video>
    </div>
    <p id="scanner-status" class="muted"></p>
    <p id="produit-info" class="produit-info"></p>

    <form method="post" class="form-inline">
        <input type="hidden" name="action" value="ajouter">
        <input type="text" name="code_barre" id="champ-code" placeholder="Code-barres" required>
        <input type="number" name="quantite" id="champ-qte" min="1" value="1" required>
        <button type="submit" class="btn-primary">Ajouter au panier</button>
    </form>
</section>

<section>
    <h2>Panier en cours (<?= count($_SESSION['panier']) ?> article(s))</h2>
    <?php if (empty($_SESSION['panier'])): ?>
        <p class="muted">Le panier est vide.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
        <tr>
            <th>Désignation</th><th>Prix unit. HT</th><th>Qté</th>
            <th>Sous-total HT</th><th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($totaux['lignes'] as $i => $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['nom']) ?> <small>(<?= htmlspecialchars($l['code_barre']) ?>)</small></td>
            <td><?= formater_montant($l['prix_unitaire_ht']) ?></td>
            <td><?= $l['quantite'] ?></td>
            <td><?= formater_montant($l['sous_total_ht']) ?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="retirer">
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <button class="btn-danger" type="submit">✖</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="3" class="ta-right"><b>Total HT</b></td><td colspan="2"><?= formater_montant($totaux['total_ht']) ?></td></tr>
        <tr><td colspan="3" class="ta-right"><b>TVA (<?= TVA_TAUX*100 ?>%)</b></td><td colspan="2"><?= formater_montant($totaux['tva']) ?></td></tr>
        <tr><td colspan="3" class="ta-right"><b>Net à payer</b></td><td colspan="2"><b><?= formater_montant($totaux['total_ttc']) ?></b></td></tr>
        </tbody>
    </table>

    <div class="actions">
        <form method="post" style="display:inline">
            <input type="hidden" name="action" value="vider">
            <button class="btn-secondary" type="submit">Vider le panier</button>
        </form>
        <form method="post" style="display:inline">
            <input type="hidden" name="action" value="valider">
            <button class="btn-success" type="submit">✓ Valider la facture</button>
        </form>
    </div>
    <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.0/umd/index.min.js"></script>
<script src="/facturation/assets/js/scanner.js"></script>
<script>
  initScanner({
    btnStart:'btn-scanner', btnStop:'btn-stop',
    container:'scanner-container', video:'video-scanner', status:'scanner-status',
    onDetect: function(code) {
      document.getElementById('champ-code').value = code;
      // Recherche du produit via AJAX
      fetch('/facturation/modules/produits/lire.php?code=' + encodeURIComponent(code))
        .then(r => r.json())
        .then(d => {
          const info = document.getElementById('produit-info');
          if (d.ok) {
            info.innerHTML = '✅ <b>' + d.produit.nom + '</b> — '
              + d.produit.prix_unitaire_ht + ' <?= DEVISE ?> (stock: ' + d.produit.quantite_stock + ')';
            info.className = 'produit-info ok';
            document.getElementById('champ-qte').focus();
          } else {
            info.innerHTML = '❌ Produit inconnu : ' + code;
            info.className = 'produit-info ko';
          }
        });
    }
  });
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
