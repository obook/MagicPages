#!/usr/bin/env bash
#
# Nom : local.sh
# Description : Lancer le site en local avec le serveur PHP intégré.
# Auteur : O. Booklage
# Date : Avril 2026
# Licence : MIT
#
set -euo pipefail

# Le port 8080 est souvent occupé par d'autres services locaux, on prend 8081.
PORT=8081
# Adresse d'écoute : on force 127.0.0.1 (IPv4) pour que le navigateur,
# qui résout "localhost" en IPv4, atteigne bien ce serveur.
HOTE=127.0.0.1

# Vérifier que PHP est installé
if ! command -v php &> /dev/null; then
  echo "Erreur : PHP n'est pas installé ou n'est pas dans le PATH."
  exit 1
fi

URL="http://${HOTE}:${PORT}"
echo "Démarrage du serveur PHP sur ${URL}"

# Ouvrir le navigateur par défaut après un court délai
(sleep 1 && xdg-open "${URL}") &

php -S "${HOTE}:${PORT}" -t www
