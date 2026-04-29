<?php
/**
 * Fonctions de gestion du catalogue produits.
 * Persistance : data/produits.json
 */

require_once __DIR__ . '/../config/config.php';

function charger_produits(): array {
    if (!file_exists(FICHIER_PRODUITS)) {
        return [];
    }
    $contenu = file_get_contents(FICHIER_PRODUITS);
    $data = json_decode($contenu, true);
    return is_array($data) ? $data : [];
}

function sauvegarder_produits(array $produits): bool {
    $json = json_encode($produits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(FICHIER_PRODUITS, $json, LOCK_EX) !== false;
}

function trouver_produit(string $code_barre): ?array {
    foreach (charger_produits() as $p) {
        if ((string)$p['code_barre'] === (string)$code_barre) {
            return $p;
        }
    }
    return null;
}

/**
 * Validation des données produit côté serveur.
 */
function valider_produit(array $donnees): array {
    $erreurs = [];

    $code = trim($donnees['code_barre'] ?? '');
    $nom  = trim($donnees['nom'] ?? '');
    $prix = $donnees['prix_unitaire_ht'] ?? '';
    $date = trim($donnees['date_expiration'] ?? '');
    $qte  = $donnees['quantite_stock'] ?? '';

    if ($code === '' || !preg_match('/^[0-9A-Za-z\-]{4,30}$/', $code)) {
        $erreurs[] = "Code-barres invalide.";
    }
    if ($nom === '' || strlen($nom) > 100) {
        $erreurs[] = "Nom du produit invalide (1-100 caractères).";
    }
    if (!is_numeric($prix) || (float)$prix <= 0) {
        $erreurs[] = "Le prix unitaire doit être un nombre positif.";
    }
    if (!is_numeric($qte) || (int)$qte < 0 || (int)$qte != $qte) {
        $erreurs[] = "La quantité doit être un entier positif ou nul.";
    }
    // Date attendue au format YYYY-MM-DD (HTML5)
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        $erreurs[] = "La date d'expiration est invalide (format AAAA-MM-JJ).";
    } elseif ($d < new DateTime(date('Y-m-d'))) {
        $erreurs[] = "La date d'expiration ne peut pas être passée.";
    }

    return $erreurs;
}

/**
 * Enregistre un nouveau produit OU met à jour un existant.
 */
function enregistrer_produit(array $donnees): array {
    $erreurs = valider_produit($donnees);
    if (!empty($erreurs)) {
        return ['ok' => false, 'erreurs' => $erreurs];
    }

    $produits = charger_produits();
    $code = $donnees['code_barre'];
    $maj = false;

    foreach ($produits as &$p) {
        if ((string)$p['code_barre'] === (string)$code) {
            $p['nom']               = $donnees['nom'];
            $p['prix_unitaire_ht']  = (float)$donnees['prix_unitaire_ht'];
            $p['date_expiration']   = $donnees['date_expiration'];
            $p['quantite_stock']    = (int)$donnees['quantite_stock'];
            $maj = true;
            break;
        }
    }
    unset($p);

    if (!$maj) {
        $produits[] = [
            'code_barre'           => $code,
            'nom'                  => $donnees['nom'],
            'prix_unitaire_ht'     => (float)$donnees['prix_unitaire_ht'],
            'date_expiration'      => $donnees['date_expiration'],
            'quantite_stock'       => (int)$donnees['quantite_stock'],
            'date_enregistrement'  => date('Y-m-d'),
        ];
    }
    sauvegarder_produits($produits);
    return ['ok' => true, 'erreurs' => [], 'mis_a_jour' => $maj];
}

/**
 * Décrémente le stock d'un produit (utilisé après facturation).
 */
function decrementer_stock(string $code_barre, int $quantite): bool {
    $produits = charger_produits();
    foreach ($produits as &$p) {
        if ((string)$p['code_barre'] === (string)$code_barre) {
            if ($p['quantite_stock'] < $quantite) {
                return false;
            }
            $p['quantite_stock'] -= $quantite;
            sauvegarder_produits($produits);
            return true;
        }
    }
    return false;
}
