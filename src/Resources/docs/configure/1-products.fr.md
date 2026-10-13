---
lang: fr
permalink: configure/products
title: Produits
description: Options de synchronisation des produits, et fonctionnement des champs personnalisés.
updated: 2026-10-13
---

L'onglet **Produits** porte les options des champs personnalisés, et elles méritent des
explications : c'est la même mécanique que l'on retrouve sur les commandes, les factures, les
articles et les pages.

![L'onglet Produits de la configuration Splash Sync](../assets/img/settings-products.png "Onglet Produits")

### Champs personnalisés

> Activé par défaut.

WordPress stocke une bonne partie de ses données dans des *post meta* : une valeur libre
attachée à un contenu, identifiée par une clé. Vos extensions en créent en permanence — un
délai de fabrication, un code fournisseur, une mention réglementaire.

Quand cette option est active, Splash expose ces valeurs comme autant de champs
supplémentaires sur vos produits, **en lecture et en écriture**. Elles deviennent alors
mappables depuis votre espace Splash, au même titre que le prix ou le stock.

Trois règles déterminent ce qui apparaît dans la liste :

- Les clés **protégées** sont ignorées. C'est la convention WordPress : toute clé commençant
  par un caractère de soulignement (`_prix_achat`, `_wc_...`) est considérée comme interne et
  n'est jamais exposée.
- Les clés techniques de Splash (`splash_id`, `splash_origin`) sont également écartées.
- Sauf si vous sélectionnez les clés à exposer (voir plus bas), la liste est plafonnée à
  **200 clés** trouvées sur vos produits et leurs variations. Au-delà, **ce sont les premières
  clés par ordre alphabétique qui sont écartées**, et un avertissement qui les liste est
  journalisé chaque fois que Splash lit la description de l'objet.

> [!NOTE]
> La liste est construite à partir des clés qui existent réellement dans votre base, pas à
> partir d'un produit en particulier. Une clé utilisée par un seul produit apparaîtra donc sur
> tous les autres, simplement vide.

> [!TIP]
> Si un champ attendu n'apparaît pas côté Splash, c'est presque toujours l'une de ces trois
> règles : clé protégée, plafond atteint, ou clé absente de la base parce qu'aucun contenu ne
> la porte encore. Sélectionner vos clés règle les deux dernières.

##### Faut-il la laisser active ?

Oui, dans la plupart des cas : c'est ce qui permet de récupérer les données posées par vos
extensions sans développement. Désactivez-la si votre site accumule des centaines de clés
techniques sans intérêt métier — vous allégerez d'autant la description des objets échangée
avec Splash. Ou gardez-la active en ne sélectionnant que les clés que vous mappez : la
description reste légère, et rien n'est écarté dans votre dos.

### Sélection des champs personnalisés

> Vide par défaut : toutes les clés sont exposées, dans la limite du plafond.

Plutôt que de laisser le plafond décider, listez ici les clés à exposer, **une par ligne** :

```
code_fournisseur
acf_*
!acf_tmp_*
```

- `code_fournisseur` — une clé exacte. Elle est exposée **avant même qu'un produit la porte** :
  votre mapping Splash ne dépend plus du contenu de votre catalogue.
- `acf_*` — toutes les clés trouvées qui commencent par `acf_`. Le joker `*` peut se placer
  n'importe où (`*_gtin`), et `*` seul sélectionne toutes les clés trouvées.
- `!acf_tmp_*` — une exclusion : les clés correspondantes ne sont jamais exposées, même si une
  autre ligne les sélectionne.
- Les lignes qui commencent par `#` sont des commentaires.

Dès qu'une clé ou un motif est sélectionné, **seules ces clés sont exposées et le plafond ne
s'applique plus**. Une liste faite uniquement d'exclusions ne fait que masquer des clés : le
plafond s'applique toujours aux autres. Les clés protégées restent masquées dans tous les cas ;
pour en ouvrir une, passez par le filtre WordPress `is_protected_meta`.

Les sites qui préfèrent le code peuvent poser ou compléter les mêmes règles avec le filtre
`splash_custom_fields_selection`, qui reçoit les règles et le type d'objet (`product`, `order`,
`invoice`, `post`, `page` ou `user`) :

```php
add_filter('splash_custom_fields_selection', function (array $rules, string $objectType): array {
    if ('product' === $objectType) {
        $rules[] = 'acf_*';
        $rules[] = '!acf_tmp_*';
    }

    return $rules;
}, 10, 2);
```
