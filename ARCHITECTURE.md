# Architecture du projet

```
www/
├── apps/           ← une application par sous-dossier
│   ├── app1/
│   │   ├── icon.png
│   │   ├── dummy.apk
│   │   └── README.md
│   └── app2/
│       └── ...
├── css/            ← feuilles de style (thème, mise en page)
├── fonts/          ← polices embarquées
├── img/            ← favicon, images et QR code
├── index.php       ← page d'accueil (liste des applications)
├── doc.php         ← affichage du README d'une application
├── magic.js        ← animations et interactions
└── parsedown.php   ← conversion Markdown → HTML
```

## Description des composants

### `index.php`

Point d'entrée du site. Parcourt le dossier `apps/`, collecte les fichiers APK, les icônes et les descriptions, puis génère la page HTML listant toutes les applications disponibles.

### `doc.php`

Affiche le contenu du fichier `README.md` d'une application donnée, converti en HTML grâce à Parsedown.

### `parsedown.php`

Bibliothèque [Parsedown](https://parsedown.org/) pour la conversion Markdown vers HTML.

### `magic.js`

Gère les animations visuelles (étoiles filantes, particules) et le basculement entre le thème sombre et le thème clair.

### `apps/`

Chaque sous-dossier représente une application. Il contient :

- un fichier `.apk` — l'application Android à télécharger ;
- un fichier `.png` — l'icône affichée sur la page d'accueil ;
- un fichier `README.md` — la documentation de l'application (le premier paragraphe sert de résumé sur la page d'accueil).

### `css/`

Feuilles de style organisées par responsabilité :

- `theme.css` — variables de couleurs et thèmes (sombre et clair) ;
- `cosmos.css` — arrière-plan animé (nébuleuses, étoiles) ;
- `chrome.css` — barre supérieure, en-tête et pied de page ;
- `markdown.css` — mise en forme du contenu Markdown ;
- `index.css` — mise en page de la liste des applications.

### `fonts/`

Polices web embarquées (Bitter, Crimson Pro, Cinzel, Inconsolata) et leur déclaration CSS (`fonts.css`).

### `img/`

Ressources graphiques : favicon SVG, logo et QR code de partage.
