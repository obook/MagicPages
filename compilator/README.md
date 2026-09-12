# Compilator

Outil de publication des applications Android du site. Pour chaque
application déclarée, il inscrit une date de fin de licence dans le source,
Kotlin ou JavaScript, compile l'APK de release et l'envoie par FTP dans le
dossier de l'application sur le serveur.

## Fichiers

| Fichier | Versionné | Rôle |
|---|---|---|
| `publier.sh` | oui | le script de publication |
| `apps.conf` | oui | liste des applications, identique sur tous les postes |
| `secret-exemple.sh` | oui | modèle des réglages propres au poste |
| `secret.sh` | **non** | identifiants FTP et emplacement des dépôts |

## Installation sur un poste

```bash
cp compilator/secret-exemple.sh compilator/secret.sh
chmod 600 compilator/secret.sh
```

Puis renseigner dans `secret.sh` :

- `FTP_SERVEUR`, `FTP_UTILISATEUR`, `FTP_MOTDEPASSE` -- mêmes valeurs que les
  secrets du dépôt GitHub qui déploie le site ;
- `FTP_CHEMIN` -- dossier `apps` du site sur le serveur, sans slash final ;
- `DOSSIER_PROJETS` -- dossier contenant les dépôts Android en local. C'est la
  seule donnée qui change d'un poste à l'autre, d'où sa place ici plutôt que
  dans `apps.conf`.

Le script refuse de démarrer si `secret.sh` est absent, incomplet, ou si ses
permissions ne sont pas `600` : il contient un mot de passe en clair.

## Déclarer une application

Une ligne par application dans `apps.conf`, quatre champs séparés par `|`,
plus deux champs facultatifs :

```
nom|source|dossier|fichier[|commande|apk]
calculatirce|calculatirce|Calculatirce|calculatirce_demo.apk
```

- `nom` -- clé passée en argument à `publier.sh` ;
- `source` -- dossier du dépôt Android, relatif à `DOSSIER_PROJETS`. Un chemin
  absolu est accepté pour un dépôt rangé ailleurs ;
- `dossier` -- dossier de l'application sur le serveur, sous `FTP_CHEMIN`. Le
  FTP est sensible à la casse : `Calculatirce` et `calculatirce` sont deux
  dossiers distincts ;
- `fichier` -- nom de l'APK sur le serveur. Il est fixe, sans horodatage :
  `index.php` liste tous les `.apk` d'un dossier, un nom variable ferait donc
  s'empiler les versions sur la page.

Les deux derniers champs ne servent qu'aux projets qui ne se compilent pas
comme un projet Gradle ordinaire :

- `commande` -- commande de compilation, lancée à la racine du dépôt.
  Défaut : `./gradlew assembleRelease` ;
- `apk` -- chemin de l'APK produit, relatif au dépôt.
  Défaut : `app/build/outputs/apk/release/app-release.apk`.

Une application web empaquetée par Capacitor, par exemple :

```
phonedetector|/chemin/phonedetector|phonedetector|phonedetector_demo.apk|npm run cap:sync && cd android && ./gradlew assembleRelease|android/app/build/outputs/apk/release/phonedetector-release.apk
```

Le dépôt doit contenir un `README.md` et porter la date de licence sur une
seule ligne, dans l'une des deux formes reconnues :

- **Kotlin** : `set(AAAA, Calendar.MOIS, JJ, 0, 0, 0)` dans un fichier sous
  `app/src`, par convention `Licence.kt` ;
- **JavaScript** : `export const EXPIRATION_DATE = ...;` dans `js/licence.js`.

L'icône du site est lue dans `media/icon.png`, facultative.

## Publier

```bash
bash compilator/publier.sh                            # toutes les applications
bash compilator/publier.sh 01/02/2027                 # date en argument
bash compilator/publier.sh 01/02/2027 calculatirce    # une seule application
bash compilator/publier.sh 01/02/2027 --simulation    # tout sauf l'envoi FTP
bash compilator/publier.sh 0/0/0 phonedetector        # version sans date limite
```

Les deux arguments sont facultatifs et reconnus à leur forme. Sans date, elle
est demandée à l'écran ; `Entrée` seul abandonne sans rien modifier.

`0/0/0` publie une version illimitée : le source reçoit la sentinelle qui
convient à son langage, `null` en JavaScript et l'an 9999 en Kotlin, et le
README publié porte "Cette version n'a pas de date limite d'utilisation".

Commencer par `--simulation` : le script va jusqu'à la compilation et affiche
les destinations FTP sans rien envoyer.

## Déroulé pour chaque application

1. **Contrôle** du dépôt : il doit exister et ne porter aucune modification
   non validée.
2. **`git pull --ff-only`**, avec la clé SSH de la forge, trois tentatives (la
   forge est instable).
3. **Date de licence** inscrite dans le fichier de licence, puis relue pour
   contrôle.
4. **Mention ajoutée en fin de `README.md`** : "Cette version de
   démonstration est utilisable jusqu'au JJ/MM/AAAA."
5. **Compilation** par la commande du projet, et copie horodatée dans `release/` du
   dépôt source.
6. **Envoi FTP** de l'APK, puis contrôle de la taille distante.
7. **Envoi FTP du `README.md`** ainsi complété : c'est lui qu'affiche
   `doc.php` sur le site. Puis de `media/icon.png`, l'icône affichée par
   `index.php` -- si le fichier manque, le script le signale et poursuit.
8. **Retrait des APK précédents** du dossier distant, après affichage de la
   liste et confirmation.
9. **Restauration** du fichier de licence et du `README.md` dans leur état
   d'origine.

À la première erreur, le script s'arrête : les applications suivantes ne sont
pas traitées, celles déjà publiées le restent.

## Points à connaître

- **Le dépôt source n'est jamais modifié durablement.** La date et la mention
  ne vivent que le temps de la compilation et de l'envoi ; un `trap` les
  efface à la sortie, y compris en cas d'erreur ou d'interruption. Rien n'est
  commité, rien n'est poussé : le dépôt garde sa date de référence, le serveur
  reçoit la version datée.
- **Aucune suppression distante en dehors des anciens APK** de l'application
  publiée, et seulement sur confirmation explicite.
- **Le mot de passe ne passe jamais par la ligne de commande** : `curl` le lit
  dans un fichier temporaire en `600`, supprimé à la sortie.
