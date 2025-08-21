# Gestion des BL – Guide d'installation rapide (XAMPP)
1) Ouvre phpMyAdmin et exécute le fichier `bl_management.sql` pour créer la base et la table.
2) Copie ces fichiers dans un même dossier sous `htdocs` (ex. `C:\xampp\htdocs\gestion-bl\`):
   - index.html
   - config.php
   - bl_api.php
   - bl_management.sql
3) Dans un navigateur, va sur `http://localhost/gestion-bl/index.html`.
4) Le formulaire "Nouveau BL" enregistre en base. La liste se charge automatiquement depuis l'API.
5) Clique sur le bouton de statut pour passer un BL de "En cours" à "Terminé" (et inversement).

• Si ton MySQL n'utilise pas `root`/mot de passe vide, édite `config.php` (variables $user et $pass).
• Si tu veux déplacer l'API ailleurs, adapte `API_URL` en haut du `<script>` dans `index.html`.
