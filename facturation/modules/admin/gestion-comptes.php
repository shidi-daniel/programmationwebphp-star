<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
exiger_role([ROLE_SUPER]);

$utilisateurs = charger_utilisateurs();
$titre_page = "Gestion des comptes";
include __DIR__ . '/../../includes/header.php';
?>
<h1>👥 Gestion des comptes utilisateurs</h1>
<p><a href="/facturation/modules/admin/ajouter-compte.php" class="btn-primary">+ Nouveau compte</a></p>

<table class="data-table">
    <thead>
    <tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Créé le</th><th>Actif</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($utilisateurs as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u['identifiant']) ?></td>
        <td><?= htmlspecialchars($u['nom_complet']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= htmlspecialchars($u['date_creation']) ?></td>
        <td><?= !empty($u['actif']) ? '✅' : '❌' ?></td>
        <td>
            <?php if ($u['role'] !== ROLE_SUPER): ?>
            <form method="post" action="/facturation/modules/admin/supprimer-compte.php"
                  onsubmit="return confirm('Supprimer ce compte ?');" style="display:inline">
                <input type="hidden" name="identifiant" value="<?= htmlspecialchars($u['identifiant']) ?>">
                <button class="btn-danger" type="submit">Supprimer</button>
            </form>
            <?php else: ?>
                <em>protégé</em>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
