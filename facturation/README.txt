================================================================
  SYSTÈME DE FACTURATION AVEC LECTURE DE CODES-BARRES
  Université Protestante au Congo - FASI L2 - 2025/2026
  TP de Programmation Web en PHP procédural
================================================================

DESCRIPTION
-----------
Application web de caisse pour un super-marché, écrite en PHP
procédural. Persistance EXCLUSIVEMENT par fichiers JSON (aucun
SGBD utilisé, conformément à la consigne).

Fonctionnalités :
  * Lecture des codes-barres via la caméra (bibliothèque ZXing)
  * Enregistrement des produits dans le catalogue
  * Création de factures avec calcul HT/TVA/TTC
  * Décrémentation automatique du stock
  * Authentification + contrôle d'accès basé sur les rôles (RBAC)
  * Rapports journaliers et mensuels

PRÉ-REQUIS
----------
  * PHP >= 7.4 (testé sur PHP 8.x)
  * Serveur web : Apache (XAMPP/WAMP/MAMP) ou serveur PHP intégré
  * Navigateur récent (Chrome, Firefox, Edge) avec accès caméra
  * Connexion Internet (pour charger ZXing depuis unpkg)

INSTALLATION (3 méthodes au choix)
----------------------------------

>> MÉTHODE 1 : Serveur PHP intégré (le plus simple) <<
   1. Décompresser le dossier "facturation/" dans un emplacement,
      par exemple : C:\projets\
   2. Ouvrir un terminal dans le dossier PARENT de "facturation/":
        cd C:\projets
   3. Lancer le serveur PHP :
        php -S localhost:8000
   4. Ouvrir http://localhost:8000/facturation/ dans le navigateur.

>> MÉTHODE 2 : XAMPP / WAMP / MAMP <<
   1. Copier le dossier "facturation/" dans htdocs/
        - XAMPP : C:\xampp\htdocs\facturation
        - WAMP  : C:\wamp64\www\facturation
        - MAMP  : /Applications/MAMP/htdocs/facturation
   2. Démarrer Apache depuis l'interface XAMPP/WAMP/MAMP.
   3. Ouvrir : http://localhost/facturation/

>> MÉTHODE 3 : Linux / Apache <<
   1. Copier dans /var/www/html/facturation/
   2. Donner les droits d'écriture sur data/ :
        sudo chown -R www-data:www-data /var/www/html/facturation/data
        sudo chmod -R 755 /var/www/html/facturation/data
   3. Ouvrir : http://localhost/facturation/

PREMIER ACCÈS
-------------
  * URL          : http://localhost[:port]/facturation/
  * Identifiant  : admin
  * Mot de passe : admin123
  * Rôle         : Super Administrateur

⚠️ CHANGEZ CE MOT DE PASSE en production !

UTILISATION DU SCANNER DE CODES-BARRES
--------------------------------------
  * La caméra nécessite HTTPS OU localhost (limitation des navigateurs).
  * Sur mobile : ouvrir l'application via http://<IP-PC>:8000/facturation/
    après avoir lancé `php -S 0.0.0.0:8000` sur le PC.
  * Cliquer sur "📷 Activer la caméra", autoriser l'accès, présenter
    un code-barres devant l'objectif.

ARBORESCENCE
------------
  facturation/
  ├── index.php                  Page d'accueil avec menu par rôle
  ├── config/config.php          Constantes (TVA, chemins, rôles)
  ├── auth/
  │   ├── login.php              Page de connexion
  │   ├── logout.php             Destruction de session
  │   └── session.php            À inclure pour protéger une page
  ├── modules/
  │   ├── produits/
  │   │   ├── enregistrer.php    Form d'enregistrement (Manager+)
  │   │   ├── lire.php           Endpoint AJAX (lookup par code)
  │   │   └── liste.php          Catalogue + niveau de stock
  │   ├── facturation/
  │   │   ├── nouvelle-facture.php  Caisse + panier + scanner
  │   │   ├── calcul.php         Endpoint AJAX (calcul totaux)
  │   │   └── afficher-facture.php  Ticket imprimable
  │   └── admin/
  │       ├── gestion-comptes.php   Liste des comptes (Super Admin)
  │       ├── ajouter-compte.php    Création de compte
  │       └── supprimer-compte.php  Suppression
  ├── data/
  │   ├── produits.json          Persistance des produits
  │   ├── factures.json          Persistance des factures
  │   └── utilisateurs.json      Comptes (mots de passe hashés)
  ├── includes/
  │   ├── header.php             Entête commune (menu)
  │   ├── footer.php             Pied de page
  │   ├── fonctions-auth.php     RBAC + sessions + comptes
  │   ├── fonctions-produits.php CRUD produits + validation
  │   └── fonctions-factures.php Calculs + génération facture
  ├── assets/
  │   ├── css/style.css          Feuille de styles
  │   └── js/scanner.js          Wrapper ZXing
  └── rapports/
      ├── rapport-journalier.php
      └── rapport-mensuel.php

RÔLES ET PERMISSIONS
--------------------
  Caissier     : Scanner, créer et consulter ses factures.
  Manager      : + Enregistrer produits, gérer stock, voir rapports.
  Super Admin  : + Créer/supprimer comptes Caissiers et Managers.

NOTES TECHNIQUES
----------------
  * Mots de passe stockés via password_hash() (bcrypt par défaut).
  * Sessions PHP avec timeout d'inactivité (30 min).
  * Validation côté serveur systématique (jamais de confiance
    aveugle au navigateur).
  * Échappement HTML systématique (htmlspecialchars) pour prévenir
    les attaques XSS.
  * Verrouillage des fichiers JSON pendant l'écriture (LOCK_EX)
    pour éviter les écritures concurrentes corrompues.
  * Aucune dépendance Composer : tout est en PHP standard.

DONNÉES DE TEST
---------------
  Au premier démarrage seul le compte 'admin' existe. Connectez-vous,
  créez quelques comptes (Manager, Caissier), puis enregistrez des
  produits avant de tester la facturation.

AUTEURS
-------
  [À compléter par les noms des étudiants du groupe]

DATE LIMITE DE REMISE : 24 avril 2026 - 23h59
DÉFENSE              : 25 avril 2026
