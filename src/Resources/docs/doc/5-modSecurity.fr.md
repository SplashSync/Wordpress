---
lang: fr
permalink: docs/modSecurity
title: Améliorer la sécurite 
---

Si vous souhaitez sécuriser votre site WordPress en protégeant les accès directs au dossier wp-content, c'est possible!!

Deux possibilité pour le faire:
* Activer le module Apache ModSecurity. Par défaut il filtrera les requètes dans le dossier wp-content/plugins.
* Utiliser un fichier .htaccess pour filtrer les requètes manuellement.

Pour pouvoir continuer à utiliser Splash, il suffit d'ajouter un fichier de redirection à la racine de votre site.

* Téléchargez-en le ici: [github.com/SplashSync/Wordpress/blob/master/src/Resources/support/splash-endpoint.php](https://raw.githubusercontent.com/SplashSync/Wordpress/master/src/Resources/support/splash-endpoint.php)
* Ajoutez le fichier à la racine de votre site /www/mon-site/splash-endpoint.php
* Sur votre compte Splash, modifiez l'adresse de votre site (Chemin du Web-service). 
* Remplacez /mon-site/wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php
* Par /mon-site/splash-endpoint.php

<div class="callout-block callout-warning">
    <div class="icon-holder">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">Attention, de plus en plus d'hébergeurs intègrent ces sécurités par défaut.</h4>
        <p>
            Si vous êtes dans ce cas, vous n'aurez pas d'autre choix que de réaliser cette manipulation. 
        </p>
    </div>
</div>