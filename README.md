# MagicPages

Site web qui présente et distribue des applications Android sous forme de fichiers APK. Chaque application dispose de sa fiche descriptive, de son icône et d'un lien de téléchargement direct.

## Fonctionnement

Le site est écrit en PHP. Il parcourt automatiquement le dossier `www/apps/` et génère la page d'accueil à partir du contenu trouvé. Pour ajouter une application, il suffit de créer un sous-dossier contenant :

- un fichier `.apk` (l'application à télécharger) ;
- un fichier `.png` (l'icône affichée sur la page) ;
- un fichier `README.md` (la description de l'application, dont le premier paragraphe sert de résumé).

## Structure du dépôt

Voir le fichier [ARCHITECTURE.md](ARCHITECTURE.md).

## Déploiement

Le déploiement est automatique : à chaque push sur la branche `main`, un workflow GitHub Actions synchronise le dossier `www/` vers le serveur FTP distant.

### Variables requises

Les secrets suivants doivent être configurés dans les paramètres du dépôt GitHub (Settings → Secrets and variables → Actions → Secrets) :

| Variable         | Description                                                                 |
|------------------|-----------------------------------------------------------------------------|
| `FTP_SERVER`     | Adresse du serveur FTP                                                      |
| `FTP_USERNAME`   | Nom d'utilisateur FTP                                                       |
| `FTP_PASSWORD`   | Mot de passe FTP                                                            |
| `FTP_PATH`       | Chemin absolu du dossier distant, terminé par `/` (ex. `/var/www/magie/`)   |

## Licence

Ce projet est distribué sous licence MIT. Voir le fichier [LICENSE](LICENSE).
