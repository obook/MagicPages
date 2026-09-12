#!/usr/bin/env bash
#
# Nom : publier.sh
# Description : Met à jour la date de fin de licence dans le source Kotlin,
#               recompile l'APK de release et le publie par FTP, pour toutes
#               les applications déclarées dans apps.conf.
# Auteur : O. Booklage
# Date : Septembre 2026
# Licence : MIT
#
# Usage : bash compilator/publier.sh [JJ/MM/AAAA] [application] [--simulation]
#         Sans date, elle est demandée à l'écran. Sans nom d'application,
#         toutes celles d'apps.conf sont traitées.
#
set -euo pipefail

DOSSIER_OUTIL="$(cd "$(dirname "$0")" && pwd)"
FICHIER_SECRET="$DOSSIER_OUTIL/secret.sh"
FICHIER_APPS="$DOSSIER_OUTIL/apps.conf"

# Variables initialisées ici car le nettoyage de sortie peut s'exécuter
# avant qu'elles ne soient renseignées.
fichier_kotlin=""
dossier_source=""
config_curl=""
readme=""

erreur() { echo "Erreur : $*" >&2; exit 1; }
etape()  { echo; echo "=== $* ==="; }
titre()  { echo; echo "########## $* ##########"; }

# Nettoyage : les fichiers modifiés du dépôt source retrouvent leur état
# d'origine et le fichier de configuration curl (qui contient le mot de
# passe) disparaît. Appelé après chaque application et à la sortie.
nettoyer() {
  if [[ -n "$dossier_source" ]]; then
    for fichier in "$fichier_kotlin" "$readme"; do
      [[ -n "$fichier" ]] \
        && git -C "$dossier_source" checkout -- "$fichier" 2>/dev/null
    done
  fi
  fichier_kotlin=""
  readme=""
  return 0
}
trap 'nettoyer; rm -f "$config_curl"' EXIT INT TERM

# Format attendu JJ/MM/AAAA, et date réellement existante : "31/02" est
# refusé, sans quoi l'APK porterait une date de fin décalée.
date_valide() {
  [[ "$1" =~ ^[0-9]{2}/[0-9]{2}/[0-9]{4}$ ]] || return 1
  local j m a
  IFS='/' read -r j m a <<<"$1"
  date -d "$a-$m-$j" > /dev/null 2>&1
}

# --- Arguments -------------------------------------------------------------

simulation="non"
date_limite=""
application=""

for argument in "$@"; do
  case "$argument" in
    --simulation) simulation="oui" ;;
    -h|--help)    sed -n '2,13p' "$0"; exit 0 ;;
    # Un argument au format d'une date est la date limite ; tout autre est
    # le nom d'une application d'apps.conf. Un argument contenant "/" est
    # forcément une date : le signaler plutôt que d'y voir une application.
    *)            if date_valide "$argument"; then
                    date_limite="$argument"
                  elif [[ "$argument" == */* ]]; then
                    erreur "date invalide : $argument (format JJ/MM/AAAA)"
                  else
                    application="$argument"
                  fi ;;
  esac
done

if [[ -z "$date_limite" ]]; then
  # Saisie interactive quand la date n'est pas passée en argument.
  while true; do
    # "|| true" : sans entrée disponible (fin de fichier), read échoue et
    # set -e interromprait le script sans le moindre message.
    read -r -p "Date limite d'utilisation (JJ/MM/AAAA, Entrée pour quitter) : " date_limite || true
    [[ -n "$date_limite" ]] || { echo; echo "Abandon."; exit 0; }
    date_valide "$date_limite" && break
    echo "Date invalide. Format attendu : JJ/MM/AAAA, par exemple 01/02/2027."
  done
fi

IFS='/' read -r jour mois annee <<<"$date_limite"

# Calendar.MONTH attend le nom anglais du mois ; 10# force la base décimale
# pour que "08" ne soit pas interprété comme de l'octal.
MOIS_CALENDAR=(JANUARY FEBRUARY MARCH APRIL MAY JUNE JULY
               AUGUST SEPTEMBER OCTOBER NOVEMBER DECEMBER)
mois_kotlin="${MOIS_CALENDAR[$((10#$mois - 1))]}"
jour_kotlin=$((10#$jour))

# --- Identifiants FTP ------------------------------------------------------

[[ -f "$FICHIER_SECRET" ]] \
  || erreur "$FICHIER_SECRET absent (copier secret-exemple.sh en secret.sh)"
[[ "$(stat -c %a "$FICHIER_SECRET")" == "600" ]] \
  || erreur "permissions de secret.sh trop ouvertes (chmod 600 $FICHIER_SECRET)"

# shellcheck source=/dev/null
source "$FICHIER_SECRET"
for variable in FTP_SERVEUR FTP_UTILISATEUR FTP_MOTDEPASSE FTP_CHEMIN DOSSIER_PROJETS; do
  [[ -n "${!variable:-}" ]] || erreur "$variable non renseignée dans secret.sh"
done
[[ -d "$DOSSIER_PROJETS" ]] \
  || erreur "DOSSIER_PROJETS introuvable sur ce poste : $DOSSIER_PROJETS"

# --- Applications à traiter ------------------------------------------------

[[ -f "$FICHIER_APPS" ]] || erreur "$FICHIER_APPS absent"

mapfile -t lignes < <(grep -vE '^[[:space:]]*(#|$)' "$FICHIER_APPS")
if [[ -n "$application" ]]; then
  mapfile -t lignes < <(printf '%s\n' "${lignes[@]}" | grep "^$application|" || true)
  [[ ${#lignes[@]} -gt 0 ]] \
    || erreur "application inconnue dans apps.conf : $application"
fi
[[ ${#lignes[@]} -gt 0 ]] || erreur "aucune application déclarée dans apps.conf"

# --- Traitement d'une application ------------------------------------------

publier_application() {
  local nom dossier_distant fichier_distant
  IFS="|" read -r nom dossier_source dossier_distant fichier_distant <<<"$1"

  # Chemin relatif : complété par DOSSIER_PROJETS, qui varie d'un poste à
  # l'autre. Chemin absolu : conservé tel quel, pour un dépôt qui ne serait
  # pas rangé avec les autres.
  [[ "$dossier_source" = /* ]] || dossier_source="$DOSSIER_PROJETS/$dossier_source"

  [[ -d "$dossier_source" ]] \
    || erreur "$nom : dépôt introuvable sur ce poste : $dossier_source"
  [[ -x "$dossier_source/gradlew" ]] \
    || erreur "$nom : $dossier_source/gradlew introuvable ou non exécutable"

  # Mise à jour du dépôt source.
  etape "Mise à jour du dépôt $dossier_source"

  [[ -z "$(git -C "$dossier_source" status --porcelain)" ]] \
    || erreur "$nom : le dépôt source contient des modifications non validées"

  # La forge est instable : une commande peut échouer puis réussir.
  export GIT_SSH_COMMAND='ssh -i ~/.ssh/id_forge -o IdentitiesOnly=yes -o IdentityAgent=none'
  local tentative
  for tentative in 1 2 3; do
    if git -C "$dossier_source" pull --ff-only; then
      break
    fi
    [[ $tentative -eq 3 ]] && erreur "$nom : git pull a échoué après 3 tentatives"
    echo "Tentative $tentative échouée, nouvel essai..."
    sleep 3
  done

  # Date de fin de licence.
  etape "Date de fin de licence : $date_limite"

  local motif_date='set\([0-9]{4}, Calendar\.[A-Z]+, [0-9]+, 0, 0, 0\)'
  fichier_kotlin="$(grep -rlE "$motif_date" --include='*.kt' "$dossier_source/app/src" || true)"
  [[ -n "$fichier_kotlin" ]] \
    || erreur "$nom : aucune date de licence trouvée dans les sources Kotlin"
  [[ "$(wc -l <<<"$fichier_kotlin")" -eq 1 ]] \
    || erreur "$nom : plusieurs fichiers Kotlin portent une date : $fichier_kotlin"

  local nouvelle_ligne="set($annee, Calendar.$mois_kotlin, $jour_kotlin, 0, 0, 0)"
  sed -i -E "s/$motif_date/$nouvelle_ligne/" "$fichier_kotlin"

  # Relecture : sans elle, une modification silencieusement ratée produirait
  # un APK à l'ancienne date.
  grep -qF "$nouvelle_ligne" "$fichier_kotlin" \
    || erreur "$nom : la date n'a pas été écrite dans $fichier_kotlin"
  echo "$(basename "$fichier_kotlin") : $nouvelle_ligne"

  # doc.php affiche le README.md du dossier distant : la mention de la date
  # limite est ajoutée en fin de fichier, avant l'envoi. Le README du dépôt
  # est restauré ensuite, comme le source Kotlin.
  readme="$dossier_source/README.md"
  [[ -f "$readme" ]] || erreur "$nom : README.md absent de $dossier_source"

  local mention="Cette version de démonstration est utilisable jusqu'au $date_limite."
  printf '\n---\n\n*%s*\n' "$mention" >> "$readme"
  echo "README.md : $mention"

  # Compilation.
  etape "Compilation de l'APK de release"

  export ANDROID_HOME="${ANDROID_HOME:-$HOME/Android/Sdk}"

  # Le JDK du système est parfois un JRE seul, sans compilateur : Gradle
  # échoue alors sur "does not provide the required capabilities:
  # [JAVA_COMPILER]". On préfère donc le JDK d'Android Studio dès qu'il est
  # présent.
  if [[ -x "$HOME/android-studio/jbr/bin/javac" ]]; then
    export JAVA_HOME="$HOME/android-studio/jbr"
  fi

  (cd "$dossier_source" && ./gradlew assembleRelease)

  local apk="$dossier_source/app/build/outputs/apk/release/app-release.apk"
  [[ -f "$apk" ]] || erreur "$nom : APK introuvable après compilation : $apk"

  # Copie locale horodatée, pour garder une trace de chaque version publiée.
  mkdir -p "$dossier_source/release"
  local archive="$dossier_source/release/${nom}_$(date '+%d%m%y_%H%M')_demo.apk"
  cp "$apk" "$archive"
  echo "Archive locale : $archive"

  # Publication FTP.
  local dossier_ftp="ftp://$FTP_SERVEUR$FTP_CHEMIN/$dossier_distant/"
  local destination="$dossier_ftp$fichier_distant"

  if [[ "$simulation" == "oui" ]]; then
    etape "Simulation : pas d'envoi FTP"
    echo "Aurait envoyé $apk vers $destination"
    echo "Aurait envoyé $readme vers ${dossier_ftp}README.md"
    if [[ -f "$dossier_source/media/icon.png" ]]; then
      echo "Aurait envoyé $dossier_source/media/icon.png vers ${dossier_ftp}icon.png"
    else
      echo "Attention : $dossier_source/media/icon.png absent, icône non envoyée."
    fi
    return 0
  fi

  etape "Envoi FTP vers $destination"

  # --progress-bar plutôt que le compteur par défaut, illisible une fois
  # mêlé aux messages du script.
  curl --config "$config_curl" --progress-bar --ftp-create-dirs \
       --upload-file "$apk" "$destination"

  # Contrôle : la taille distante doit correspondre à la taille locale.
  local taille_locale taille_distante
  taille_locale="$(stat -c %s "$apk")"
  taille_distante="$(curl --config "$config_curl" --silent --head "$destination" \
    | grep -i '^Content-Length:' | tr -d '\r' | awk '{print $2}')"

  [[ "$taille_distante" == "$taille_locale" ]] \
    || erreur "$nom : taille distante ($taille_distante) différente de la locale ($taille_locale)"

  echo "Envoi vérifié : $taille_locale octets sur le serveur."

  etape "Envoi du README"

  curl --config "$config_curl" --silent --show-error \
       --upload-file "$readme" "${dossier_ftp}README.md"
  echo "README.md publié ($(stat -c %s "$readme") octets)."

  # index.php prend le premier .png du dossier comme icône de l'application.
  # Son absence ne casse pas la page, d'où un simple avertissement.
  local icone="$dossier_source/media/icon.png"
  if [[ -f "$icone" ]]; then
    etape "Envoi de l'icône"
    curl --config "$config_curl" --silent --show-error \
         --upload-file "$icone" "${dossier_ftp}icon.png"
    echo "icon.png publié ($(stat -c %s "$icone") octets)."
  else
    echo "Attention : $icone absent, icône non envoyée."
  fi

  # Retrait des versions précédentes : index.php liste tous les .apk du
  # dossier, l'ancienne resterait proposée au téléchargement.
  etape "Anciens APK du dossier $dossier_distant"

  local anciens reponse ancien
  anciens="$(curl --config "$config_curl" --silent --show-error --list-only "$dossier_ftp" \
    | tr -d '\r' | grep -E '\.apk$' | grep -vFx "$fichier_distant" || true)"

  if [[ -z "$anciens" ]]; then
    echo "Aucune version précédente à retirer."
  else
    echo "$anciens"
    read -r -p "Supprimer ces fichiers du serveur ? [o/N] " reponse
    if [[ "${reponse,,}" == "o" ]]; then
      while IFS= read -r ancien; do
        curl --config "$config_curl" --silent --show-error \
             --quote "DELE $FTP_CHEMIN/$dossier_distant/$ancien" \
             "$dossier_ftp" > /dev/null \
          || erreur "$nom : suppression impossible de $ancien"
        echo "Supprimé : $ancien"
      done <<<"$anciens"
    else
      echo "Conservés : la page listera plusieurs APK pour cette application."
    fi
  fi
}

# --- Boucle sur les applications -------------------------------------------

if [[ "$simulation" != "oui" ]]; then
  # Le mot de passe passe par un fichier de configuration en 600 et non par
  # la ligne de commande, qui serait lisible par tout utilisateur via "ps".
  config_curl="$(mktemp)"
  chmod 600 "$config_curl"
  printf 'user = "%s:%s"\n' "$FTP_UTILISATEUR" "$FTP_MOTDEPASSE" > "$config_curl"
fi

numero=0
for ligne in "${lignes[@]}"; do
  numero=$((numero + 1))
  titre "Application $numero/${#lignes[@]} : ${ligne%%|*}"
  publier_application "$ligne"
  # Restauration immédiate : le dépôt doit être propre pour le prochain
  # lancement, et l'application suivante ne doit rien hériter de celle-ci.
  nettoyer
done

echo
echo "Terminé : $numero application(s) traitée(s), date limite $date_limite."
