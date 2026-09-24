---
lang: fr
permalink: docs/modSecurity
title: Améliorer la sécurité
description: Gardez Splash opérationnel lorsque l'accès direct au dossier wp-content est filtré.
updated: 2026-09-24
translation:
    from:   en
    mode:   human
---

Vous pouvez protéger votre site WordPress en bloquant les accès directs au dossier
`wp-content`, soit en activant le module Apache **ModSecurity** — qui filtre par défaut les
requêtes vers `wp-content/plugins` — soit en les filtrant vous-même depuis un fichier
`.htaccess`.

Splash joint votre site via un fichier situé précisément dans ce dossier : un tel filtre coupe
donc la connexion. L'ajout d'un petit fichier de redirection à la racine de votre site la
rétablit.

### Mettre en place la redirection

1. Téléchargez [splash-endpoint.php](https://raw.githubusercontent.com/SplashSync/Wordpress/2.0/src/Resources/support/splash-endpoint.php)
   depuis le [dépôt du plugin](https://github.com/SplashSync/Wordpress/blob/2.0/src/Resources/support/splash-endpoint.php).
2. Déposez-le à la racine de votre site, sous `/www/mon-site/splash-endpoint.php`.
3. Sur votre compte Splash, modifiez votre serveur et remplacez le **chemin du webservice**,
   de `/mon-site/wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php`
   vers `/mon-site/splash-endpoint.php`.

> [!WARNING]
> De plus en plus d'hébergeurs activent ce type de filtrage par défaut. Si c'est votre cas,
> cette redirection n'est pas une option : c'est le seul moyen de garder Splash connecté.
