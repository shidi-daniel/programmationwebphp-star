<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/fonctions-auth.php';

$erreur = '';
$timeout = isset($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = trim($_POST['identifiant'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    if ($id === '' || $mdp === '') {
        $erreur = "Veuillez renseigner identifiant et mot de passe.";
    } elseif (connecter_utilisateur($id, $mdp)) {
        header('Location: /facturation/index.php');
        exit;
    } else {
        $erreur = "Identifiants invalides ou compte désactivé.";
    }
}
$titre_page = "Connexion";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - <?= NOM_COMMERCE ?></title>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <h1><?= NOM_COMMERCE ?></h1>
        <h2>Connexion</h2>
        <?php if ($timeout): ?><p class="info">Session expirée, reconnectez-vous.</p><?php endif; ?>
        <?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
        <form method="post" autocomplete="off">
            <label>Identifiant
                <input type="text" name="identifiant" required autofocus
                       value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>">
            </label>
            <label>Mot de passe
                <input type="password" name="mot_de_passe" required>
            </label>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
        <p class="hint">Compte initial : <code>admin</code> / <code>admin123</code></p>
    </div>
</body>
</html>
