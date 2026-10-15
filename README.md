# GameCafe Manager

Application web de gestion d'un gaming café : postes (PC / PS5 / VR), clients, sessions de jeu, ventes de produits et chiffre d'affaires.

**Stack** : MySQL · PHP (PDO) · HTML/CSS

## Base de données
5 tables : `clients`, `postes`, `produits`, `sessions`, `ventes` (clés étrangères, contraintes CHECK, index).
- **Triggers** : poste occupé au début d'une session, calcul automatique du montant à la fin, décrémentation du stock à chaque vente.
- **Vues** : `v_ca_journalier` (chiffre d'affaires par jour), `v_top_clients`.

## Lancer le projet (XAMPP)
1. Copier le dossier dans `C:\xampp\htdocs\gamecafe`
2. Démarrer Apache et MySQL, puis importer `database.sql` dans phpMyAdmin
3. Ouvrir http://localhost/gamecafe/index.php

## Fonctionnalités
- Dashboard : CA du jour, postes libres, nombre de clients
- Démarrer / terminer une session sur un poste
- CRUD clients avec total dépensé
