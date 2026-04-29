<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
exiger_role([ROLE_SUPER]);

$erreurs = []; $succes = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = creer_compte(
        trim($_POST['identifiant'] ?? ''),
        $_POST['mot_de_passe'] ?? '',
        $_POST['role'] ?? '',
        trim($_POST['nom_complet'] ?? '')
    );
    if ($res['ok']) {
        $succes = "Compte créé avec succès.";
    } else {
        $erreurs = $res['erreurs'];
    }
}
$titre_page = "Ajouter un compte";
include __DIR__ . '/../../includes/header.php';
?>
<h1>➕ Ajouter un compte utilisateur</h1>
<?php if ($succes): ?><div class="alert-success"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
<?php if ($erreurs): ?>
    <div class="alert-error">
        <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" class="form-produit">
    <label>Identifiant <input type="text" name="identifiant" required pattern="[A-Za-z0-9._-]{3,30}"></label>
    <label>Nom complet <input type="text" name="nom_complet" required></label>
    <label>Mot de passe <input type="password" name="mot_de_passe" required minlength="6"></label>
    <label>Rôle
        <select name="role" required>
            <option value="caissier">Caissier</option>
            <option value="manager">Manager</option>
        </select>
    </label>
    <button type="submit" class="btn-primary">Créer le compte</button>
    <a href="/facturation/modules/admin/gestion-comptes.php" class="btn-secondary">Annuler</a>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
