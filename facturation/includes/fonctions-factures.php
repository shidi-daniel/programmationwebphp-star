<?php
/**
 * Fonctions de gestion des factures.
 * Persistance : data/factures.json
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions-produits.php';

function charger_factures(): array {
    if (!file_exists(FICHIER_FACTURES)) {
        return [];
    }
    $contenu = file_get_contents(FICHIER_FACTURES);
    $data = json_decode($contenu, true);
    return is_array($data) ? $data : [];
}

function sauvegarder_factures(array $factures): bool {
    $json = json_encode($factures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(FICHIER_FACTURES, $json, LOCK_EX) !== false;
}

/**
 * Génère un identifiant unique de facture : FAC-YYYYMMDD-XXX
 */
function generer_id_facture(): string {
    $date = date('Ymd');
    $factures = charger_factures();
    $compteur = 1;
    foreach ($factures as $f) {
        if (strpos($f['id_facture'], "FAC-$date-") === 0) {
            $compteur++;
        }
    }
    return sprintf('FAC-%s-%03d', $date, $compteur);
}

/**
 * Calcule les totaux d'une liste d'articles.
 * @param array $articles [['code_barre','nom','prix_unitaire_ht','quantite'], ...]
 */
function calculer_totaux(array $articles): array {
    $lignes = [];
    $total_ht = 0.0;
    foreach ($articles as $a) {
        $sous = (float)$a['prix_unitaire_ht'] * (int)$a['quantite'];
        $total_ht += $sous;
        $lignes[] = [
            'code_barre'        => $a['code_barre'],
            'nom'               => $a['nom'],
            'prix_unitaire_ht'  => (float)$a['prix_unitaire_ht'],
            'quantite'          => (int)$a['quantite'],
            'sous_total_ht'     => $sous,
        ];
    }
    $tva = round($total_ht * TVA_TAUX, 2);
    $ttc = round($total_ht + $tva, 2);
    return [
        'lignes'    => $lignes,
        'total_ht'  => round($total_ht, 2),
        'tva'       => $tva,
        'total_ttc' => $ttc,
    ];
}

/**
 * Enregistre une nouvelle facture et décrémente les stocks.
 * @return array ['ok' => bool, 'facture' => array|null, 'erreurs' => array]
 */
function enregistrer_facture(array $articles_panier, string $identifiant_caissier): array {
    $erreurs = [];
    if (empty($articles_panier)) {
        return ['ok' => false, 'facture' => null, 'erreurs' => ["Le panier est vide."]];
    }

    // Vérification stock + récupération info produit
    $articles_complets = [];
    foreach ($articles_panier as $a) {
        $produit = trouver_produit($a['code_barre']);
        if ($produit === null) {
            $erreurs[] = "Produit inconnu : {$a['code_barre']}";
            continue;
        }
        if ((int)$a['quantite'] <= 0) {
            $erreurs[] = "Quantité invalide pour {$produit['nom']}";
            continue;
        }
        if ($produit['quantite_stock'] < (int)$a['quantite']) {
            $erreurs[] = "Stock insuffisant pour {$produit['nom']} (dispo: {$produit['quantite_stock']})";
            continue;
        }
        $articles_complets[] = [
            'code_barre'        => $produit['code_barre'],
            'nom'               => $produit['nom'],
            'prix_unitaire_ht'  => $produit['prix_unitaire_ht'],
            'quantite'          => (int)$a['quantite'],
        ];
    }
    if (!empty($erreurs)) {
        return ['ok' => false, 'facture' => null, 'erreurs' => $erreurs];
    }

    $totaux = calculer_totaux($articles_complets);

    $facture = [
        'id_facture' => generer_id_facture(),
        'date'       => date('Y-m-d'),
        'heure'      => date('H:i:s'),
        'caissier'   => $identifiant_caissier,
        'articles'   => $totaux['lignes'],
        'total_ht'   => $totaux['total_ht'],
        'tva'        => $totaux['tva'],
        'total_ttc'  => $totaux['total_ttc'],
    ];

    // Décrémente le stock
    foreach ($facture['articles'] as $ligne) {
        decrementer_stock($ligne['code_barre'], $ligne['quantite']);
    }

    $factures = charger_factures();
    $factures[] = $facture;
    sauvegarder_factures($factures);

    return ['ok' => true, 'facture' => $facture, 'erreurs' => []];
}

function trouver_facture(string $id): ?array {
    foreach (charger_factures() as $f) {
        if ($f['id_facture'] === $id) {
            return $f;
        }
    }
    return null;
}

function formater_montant(float $m): string {
    return number_format($m, 0, ',', ' ') . ' ' . DEVISE;
}
