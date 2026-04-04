/*
 * Nom : magic.js
 * Description : Basculement de thème et animation d'étoiles filantes.
 * Auteur : O. Booklage
 * Date : Avril 2026
 * Licence : MIT
 */

/* ── Basculement de thème ── */
function toggleTheme() {
  var estClair = document.documentElement.getAttribute('data-theme') === 'light';
  if (estClair) {
    document.documentElement.removeAttribute('data-theme');
    localStorage.removeItem('theme');
  } else {
    document.documentElement.setAttribute('data-theme', 'light');
    localStorage.setItem('theme', 'light');
  }
}

/* ── Étoiles filantes ── */
(function () {
  var canvas = document.getElementById('shootingStars');
  if (!canvas) {
    return;
  }
  var contexte = canvas.getContext('2d');
  var etoiles = [];
  var largeur;
  var hauteur;

  function redimensionner() {
    largeur = canvas.width = window.innerWidth;
    hauteur = canvas.height = window.innerHeight;
  }
  redimensionner();
  window.addEventListener('resize', redimensionner);

  var couleurs = [
    { r: 212, g: 168, b: 68 },
    { r: 232, g: 226, b: 244 },
    { r: 168, g: 126, b: 223 }
  ];

  /* Créer une nouvelle étoile filante avec une position et une trajectoire aléatoires */
  function creerEtoile() {
    var cote = Math.random();
    var posX;
    var posY;

    if (cote < 0.7) {
      posX = Math.random() * largeur * 1.2 - largeur * 0.1;
      posY = -10;
    } else {
      posX = largeur + 10;
      posY = Math.random() * hauteur * 0.5;
    }

    var angle = (200 + Math.random() * 40) * Math.PI / 180;
    var vitesse = 6 + Math.random() * 10;
    var dureeMax = 40 + Math.random() * 50;
    var couleur = couleurs[Math.floor(Math.random() * couleurs.length)];
    var longueurTrainee = 18 + Math.floor(Math.random() * 14);

    etoiles.push({
      x: posX,
      y: posY,
      vx: Math.cos(angle) * vitesse,
      vy: -Math.sin(angle) * vitesse,
      vie: 0,
      dureeMax: dureeMax,
      couleur: couleur,
      epaisseur: 1 + Math.random() * 1.5,
      trainee: [],
      longueurTrainee: longueurTrainee
    });
  }

  /* Dessiner toutes les étoiles et leur traînée */
  function dessiner() {
    contexte.clearRect(0, 0, largeur, hauteur);

    for (var i = etoiles.length - 1; i >= 0; i--) {
      var etoile = etoiles[i];
      etoile.x += etoile.vx;
      etoile.y += etoile.vy;
      etoile.vie++;

      etoile.trainee.push({ x: etoile.x, y: etoile.y });
      if (etoile.trainee.length > etoile.longueurTrainee) {
        etoile.trainee.shift();
      }

      var progression = etoile.vie / etoile.dureeMax;
      var opacite;
      if (progression < 0.12) {
        opacite = progression / 0.12;
      } else if (progression > 0.55) {
        opacite = 1 - (progression - 0.55) / 0.45;
      } else {
        opacite = 1;
      }
      opacite = Math.max(0, Math.min(1, opacite)) * 0.85;

      dessinerTrainee(etoile, opacite);
      dessinerPointLumineux(etoile, opacite);

      /* Supprimer l'étoile si elle a dépassé sa durée de vie ou quitté l'écran */
      var horsEcran = etoile.x < -200 || etoile.x > largeur + 200 || etoile.y > hauteur + 200;
      if (etoile.vie >= etoile.dureeMax || horsEcran) {
        etoiles.splice(i, 1);
      }
    }

    requestAnimationFrame(dessiner);
  }

  /* Tracer les segments de la traînée avec un dégradé progressif */
  function dessinerTrainee(etoile, opacite) {
    var nbPoints = etoile.trainee.length;
    if (nbPoints <= 1) {
      return;
    }

    for (var j = 1; j < nbPoints; j++) {
      var ratio = j / (nbPoints - 1);
      var opaciteSegment = opacite * ratio * ratio;
      var largeurSegment = etoile.epaisseur * (0.15 + 0.85 * ratio);

      var coul = etoile.couleur;
      var rouge = Math.round(coul.r + (255 - coul.r) * ratio * 0.5);
      var vert = Math.round(coul.g + (255 - coul.g) * ratio * 0.5);
      var bleu = Math.round(coul.b + (255 - coul.b) * ratio * 0.5);

      contexte.beginPath();
      contexte.moveTo(etoile.trainee[j - 1].x, etoile.trainee[j - 1].y);
      contexte.lineTo(etoile.trainee[j].x, etoile.trainee[j].y);
      contexte.strokeStyle = 'rgba(' + rouge + ',' + vert + ',' + bleu + ',' + opaciteSegment + ')';
      contexte.lineWidth = largeurSegment;
      contexte.lineCap = 'round';
      contexte.stroke();
    }
  }

  /* Dessiner le point lumineux au bout de l'étoile */
  function dessinerPointLumineux(etoile, opacite) {
    contexte.beginPath();
    contexte.arc(etoile.x, etoile.y, etoile.epaisseur * 1.2, 0, Math.PI * 2);
    contexte.fillStyle = 'rgba(255,255,255,' + opacite * 0.7 + ')';
    contexte.fill();

    contexte.beginPath();
    contexte.arc(etoile.x, etoile.y, etoile.epaisseur * 0.5, 0, Math.PI * 2);
    contexte.fillStyle = 'rgba(255,255,255,' + opacite + ')';
    contexte.fill();
  }

  /* Planifier l'apparition de la prochaine étoile avec un délai aléatoire */
  function planifierSuivante() {
    var delai = 2000 + Math.random() * 6000;
    setTimeout(function () {
      creerEtoile();
      if (Math.random() < 0.2) {
        setTimeout(creerEtoile, 100 + Math.random() * 300);
        if (Math.random() < 0.5) {
          setTimeout(creerEtoile, 200 + Math.random() * 400);
        }
      }
      planifierSuivante();
    }, delai);
  }

  setTimeout(creerEtoile, 1000);
  planifierSuivante();
  dessiner();
})();
