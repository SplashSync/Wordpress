---
lang: fr
permalink: configure/advanced
title: Avancé
description: Mode avancé, adresse du serveur Splash et protocole de communication.
updated: 2026-09-24
---

Ces paramètres se trouvent au bas de l'onglet **Connexion**. Ils modifient la façon dont votre
site joint nos serveurs.

> [!CAUTION]
> Ne les utilisez que sur notre demande. Une valeur erronée coupe la communication avec Splash,
> sans message d'erreur explicite côté WordPress : votre serveur cesse simplement de répondre.

### Activer le mode avancé

> Désactivé par défaut.

Déverrouille les deux paramètres suivants. Tant que cette case est décochée, le plugin utilise
ses valeurs par défaut, qui conviennent à la quasi-totalité des installations.

### URL du serveur

> Valeur par défaut : `www.splashsync.com/ws/soap`

L'adresse du serveur Splash que votre site contacte. Vous n'avez de raison de la changer que
dans deux cas : notre support vous oriente vers une infrastructure dédiée, ou vous montez un
environnement de test contre un serveur local.

### Protocole

> Valeur par défaut : `Generic PHP SOAP`

Le transport utilisé pour dialoguer avec Splash. Tant que le mode avancé reste désactivé, le
plugin s'appuie sur l'implémentation SOAP native de PHP, quelle que soit la valeur affichée
dans la liste déroulante.

| Protocole | Quand l'utiliser |
|---|---|
| **Generic PHP SOAP** | Valeur par défaut et choix recommandé. S'appuie sur l'extension PHP `soap`. |
| **NuSOAP Librairie** | Implémentation embarquée, à réserver aux hébergements dépourvus de l'extension `soap`. |

> [!WARNING]
> La liste déroulante présélectionne **NuSOAP** tant qu'aucune valeur n'a été enregistrée, alors
> que le protocole réellement utilisé est `Generic PHP SOAP`. Activer le mode avancé puis
> enregistrer sans toucher à ce champ bascule donc votre site sur NuSOAP sans prévenir.
> Sélectionnez explicitement le protocole voulu avant d'enregistrer.

### Vérifier après chaque changement

Tout enregistrement dans cet onglet relance les self-tests et une tentative de connexion. Passez
par l'onglet **Informations** pour confirmer que le ping et la connexion au serveur répondent
toujours avant de quitter la page.
