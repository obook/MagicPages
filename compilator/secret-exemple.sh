#!/usr/bin/env bash
#
# Nom : secret-exemple.sh
# Description : Modèle des réglages propres au poste : identifiants FTP et
#               emplacement des dépôts Android.
#               Copier ce fichier en "secret.sh" puis renseigner les valeurs.
# Auteur : O. Booklage
# Date : Septembre 2026
# Licence : MIT
#
# secret.sh n'est PAS versionné (il contient le mot de passe en clair).
# Après copie : chmod 600 compilator/secret.sh

FTP_SERVEUR="ftp.exemple.net"
FTP_UTILISATEUR="changez-moi"
FTP_MOTDEPASSE="changez-moi"
# Dossier "apps" du site sur le serveur, sans slash final : c'est lui qui
# contient un sous-dossier par application.
FTP_CHEMIN="/android/magie/apps"

# Dossier contenant les dépôts Android en local, sans slash final. Il varie
# d'un poste à l'autre, alors qu'apps.conf est identique partout : c'est
# pourquoi il est réglé ici. Surcharge ponctuelle possible :
#   DOSSIER_PROJETS=/autre/chemin bash compilator/publier.sh
DOSSIER_PROJETS="${DOSSIER_PROJETS:-$HOME/Documents/GitForge}"
