---
lang: fr
permalink: overview
title: Splash pour WordPress & WooCommerce
description: Connectez votre site WordPress et votre boutique WooCommerce à toutes vos autres applications.
updated: 2026-09-24
translation:
    from:   en
    mode:   human
---

Votre boutique n'est pas une île. Vos stocks vivent aussi dans votre ERP, vos clients dans
votre CRM, vos factures dans votre comptabilité — et chacune de ces copies s'éloigne un peu
plus des autres chaque jour. Ressaisir les mêmes données prend du temps, mais ce qu'elles
coûtent vraiment, ce sont les erreurs : un prix mis à jour à un seul endroit, une commande
facturée deux fois, un stock qui annonce 3 quand le rayon est vide.

Le **Splash Connector** met fin à tout ça. Installez-le, reliez-le à votre compte Splash, et
votre site WordPress devient une application de plus dans un réseau où les données circulent
toutes seules. Sans développement, sans script planifié, sans fichier d'export à surveiller. :rocket:

### Ce qui est synchronisé

| Objet | Ce qu'il couvre | WooCommerce requis |
|---|---|---|
| **Clients** | Comptes, coordonnées, adresses de facturation et de livraison | oui |
| **Adresses** | Adresses des clients, et adresses propres à chaque commande | oui |
| **Produits** | Catalogue, déclinaisons, prix, stocks, images, codes-barres, coût de revient | oui |
| **Commandes** | Lignes, statuts, totaux, frais de port et frais divers | oui |
| **Factures** | Factures, dates de paiement, montants | oui |
| **Articles & Pages** | Contenus, extraits, médias, statut de publication | non |

Sur un WordPress nu, Splash gère déjà vos articles et vos pages. Tout le reste se débloque dès
que WooCommerce est actif.

### Pourquoi ce n'est pas un énième plugin point à point

La plupart des connecteurs relient une boutique à un logiciel. Ajoutez une troisième
application, il vous faut un troisième plugin, avec sa configuration, ses bugs et son rythme de
mises à jour.

Splash prend le problème à l'envers : chaque application dialogue avec un **hub unique**, dans
un langage de données commun. Connectez WooCommerce une fois, et il peut échanger avec
Dolibarr, PrestaShop, Magento, Sylius, un CRM ou un outil marketing — aujourd'hui, et avec ce
que vous ajouterez l'an prochain, sans jamais retoucher ce plugin.

### Vous décidez ce qui circule, et dans quel sens

La synchronisation n'est jamais du tout ou rien. Depuis votre espace Splash, vous choisissez
**champ par champ** ce qui sort de votre site et ce qui y entre.

- Laissez votre ERP maîtriser les prix, pendant que WooCommerce garde la main sur les
  descriptions et les photos.
- Poussez vos stocks vers l'extérieur, et refusez toute modification entrante.
- Acceptez les nouveaux clients venus de votre CRM, sans jamais l'autoriser à écraser un compte
  existant.

Le plugin sait aussi se taire : désactivez les adresses de vos clients, les champs
personnalisés de vos commandes ou vos pages en un clic si elles n'ont rien à faire hors de
WordPress.

### Ce que ça change au quotidien

**Des stocks qui disent la vérité.** Une vente en boutique, une livraison réceptionnée dans
l'ERP, un retour enregistré au comptoir : chaque mouvement se répercute partout en quelques
secondes. La survente cesse d'être une fatalité. :package:

**De la facturation sans ressaisie.** Commandes et factures arrivent dans votre comptabilité
avec leurs lignes, leurs taxes, leurs dates de paiement et leurs adresses. Votre comptable
n'attend plus l'export mensuel.

**Un client, une fiche.** La même personne achète sur votre boutique, appelle votre support et
se fait facturer par votre ERP. Le Linker de Splash fusionne ces profils en une seule entité :
une adresse corrigée une fois est corrigée partout.

**Plusieurs sites, un seul catalogue.** Vous gérez deux boutiques, ou une boutique et une place
de marché ? Publiez une fois, laissez Splash répliquer le catalogue, et gardez les stocks
alignés entre les deux.

### Pensé pour de vraies boutiques

Le plugin est testé sur toutes les versions de WordPress supportées, de la **6.3 à la 7.0**, de
PHP 7.4 à 8.3, avec la version de WooCommerce correspondant à chacune. Il respecte les droits
WordPress : le compte que vous confiez à Splash ne peut faire que ce que vous l'autorisez à
faire.

Il connaît également les extensions que vous utilisez déjà — WooCommerce Bookings, Dokan, WPML,
WP Multilang, Wholesale Prices — et adapte ce qu'il expose en conséquence.

### Opérationnel en quelques minutes

Installez le plugin, collez les deux clés de votre compte Splash, vérifiez que les self-tests
sont au vert. Toute la configuration tient là. :sparkles:

Ce plugin fait partie du projet [SplashSync](https://www.splashsync.com), et il est développé
au grand jour sur [GitHub](https://github.com/SplashSync/Wordpress). Toutes les Pull Requests
sont les bienvenues.
