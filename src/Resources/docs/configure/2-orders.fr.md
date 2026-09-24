---
lang: fr
permalink: configure/orders
title: Commandes & Factures
description: Options de ligne dans les libellés, champs personnalisés et adresses propres aux commandes.
updated: 2026-09-24
---

L'onglet **Commandes** rassemble les options des commandes *et* des factures : dans
WooCommerce, une facture n'est rien d'autre qu'une commande vue sous un autre angle.

![L'onglet Commandes de la configuration Splash Sync](../assets/img/settings-orders.png "Onglet Commandes")

### Options dans les libellés d'articles

> Activé par défaut.

Une ligne de commande WooCommerce transporte souvent des informations au-delà du produit
lui-même : la déclinaison choisie, une gravure, une date de réservation, une option posée par
une extension d'add-ons. Ces valeurs vivent dans les métadonnées de la ligne, et la plupart des
logiciels de destination ne savent pas quoi en faire.

Quand cette option est active, Splash les **ajoute au libellé de l'article, entre
parenthèses** :

```
Tee-shirt coton bio (Taille : L, Couleur : Marine)
```

Votre ERP ou votre comptabilité reçoivent alors une désignation complète, lisible telle quelle
sur un bon de livraison ou une facture, sans aucun mapping supplémentaire.

> [!NOTE]
> Les options restent également disponibles dans un champ dédié, séparé du libellé. Désactivez
> cette option si votre logiciel de destination sait exploiter ce champ lui-même, ou si vous
> tenez à des libellés strictement identiques à votre catalogue.

### Champs personnalisés des commandes

> Désactivé par défaut.

Expose les métadonnées de vos commandes comme champs supplémentaires, en lecture et en
écriture. Le mécanisme, ses exclusions et son plafond de 200 clés sont décrits sur la page
**Produits** de cette section.

Cette option est désactivée par défaut car les commandes portent énormément de métadonnées
techniques, posées par les passerelles de paiement et les modules de livraison. Ne l'activez
que si vous avez identifié une donnée précise à faire circuler.

### Champs personnalisés des factures

> Désactivé par défaut.

Même principe, appliqué à l'objet Facture. Commandes et factures partageant le même
enregistrement WooCommerce, activer les deux expose deux fois les mêmes clés : ne le faites que
si vos mappings Splash diffèrent réellement entre les deux objets.

### Adresses de livraison des commandes

> Désactivé par défaut.

WooCommerce enregistre sur chaque commande une copie figée de l'adresse de livraison, telle
qu'elle était au moment de l'achat. Elle ne bouge plus ensuite, même si le client modifie son
compte.

Activez cette option pour exposer ces adresses comme des objets **Adresse à part entière**, en
lecture seule.

> [!IMPORTANT]
> Ce sont les adresses **de la commande**, pas celles du compte client. C'est précisément leur
> intérêt : elles reflètent l'état réel au moment de la vente, ce qu'attendent une comptabilité
> et un transporteur.

Elles sont en lecture seule par construction : réécrire l'adresse d'une commande passée
reviendrait à réécrire l'histoire. Toute tentative d'écriture est refusée et signalée dans les
journaux Splash.

### Adresses de facturation des commandes

> Désactivé par défaut.

Exactement la même chose, appliqué au volet facturation de la commande.

> [!TIP]
> Ces deux options augmentent le nombre d'objets Adresse visibles par Splash : à chaque
> commande ses adresses. Sur une boutique à fort volume, n'activez que le volet dont vous avez
> réellement besoin.
