#!/usr/bin/env bash
#
# Nom : local.sh
# Description : Lancer le site en local avec le serveur PHP intégré.
# Auteur : O. Booklage
# Date : Avril 2026
# Licence : MIT
#
set -euo pipefail

PORT=8080

# Vérifier que PHP est installé
if ! command -v php &> /dev/null; then
  echo "Erreur : PHP n'est pas installé ou n'est pas dans le PATH."
  exit 1
fi

URL="http://localhost:${PORT}"
echo "Démarrage du serveur PHP sur ${URL}"

# Ouvrir le navigateur par défaut après un court délai
(sleep 1 && xdg-open "${URL}") &

php -S "localhost:${PORT}" -t www
