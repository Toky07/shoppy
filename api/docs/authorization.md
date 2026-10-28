# Autorisation

L’API sépare **l’authentification** (qui appelle) de **l’autorisation** (ce qu’il a le droit de faire).

## Authentification

- **Identifiants** : `Authorization: Bearer <token>` ou le cookie `shoppy_session` posé à la connexion.
- **Entrée** : `AccessTokenAuthenticator` valide le jeton et construit un `AuthenticatedUser` dans le token store Symfony Security.
- **Auth optionnelle** : un jeton invalide ou expiré sur une route **publique** est traité comme anonyme (pas de 401). Les routes protégées utilisent `AuthorizationAttributes::IS_AUTHENTICATED` et répondent **401** s’il n’y a pas de session valide.
- **Côté application** : injecter `CurrentUser` pour l’id utilisateur, le jeton de session (déconnexion) et `isAuthenticated()`.

## Rôles

| Rôle Symfony   | Rôle domaine | Usage                              |
|----------------|--------------|------------------------------------|
| `ROLE_ADMIN`   | `admin`      | Back-office, écriture catalogue    |
| `ROLE_USER`    | `customer`   | Tout compte connecté               |

Admin implique user (`role_hierarchy` dans `security.yaml`).

Les routes admin globales portent `#[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]` sur le contrôleur.

Les rôles Symfony de `AuthenticatedUser` sont mappés vers le domaine via `domainRole()` (`admin` / `customer`) pour les attributs de requête consommés par `CurrentUser`.

## Registre d’attributs

| Constante | Valeur Symfony / voter | Usage typique |
|-----------|------------------------|---------------|
| `IS_AUTHENTICATED` | `IS_AUTHENTICATED_FULLY` | Panier, compte, commandes du client |
| `ROLE_ADMIN` | `ROLE_ADMIN` | CRUD admin, liste de toutes les commandes |
| `ORDER_VIEW` | `ORDER_VIEW` | Lire une commande (propriétaire ou admin) |
| `ORDER_CANCEL` | `ORDER_CANCEL` | Annuler une commande (propriétaire uniquement) |
| `USER_VIEW` | `USER_VIEW` | Profil utilisateur (soi-même ou admin) |
| `PAYMENT_ACCESS` | `PAYMENT_ACCESS` | Flux de paiement (propriétaire uniquement) |
| `PRODUCT_ADMIN` | `PRODUCT_ADMIN` | Brouillons / catalogue non publié |
| `MEDIA_LIST` | `MEDIA_LIST` | Listes média hors produit |

## Voters de ressource

Quand le **sujet** du voter vient de l’URL (ou d’un champ de requête connu), poser `#[IsGranted(..., subject: '…')]` sur l’action, avec les **argument value resolvers** qui construisent `OrderAccessSubject`, `UserAccessSubject` ou `PaymentAccessSubject`. Les chargements de commande sont dédupliqués via `OrderResponseRequestCache`.

Utiliser `GrantChecker` pour les contrôles qui dépendent de flags de query (produits brouillon) ou qui doivent transformer un refus d’auth en une autre erreur métier (produit non publié → `ProductNotFound`).

| Attribut         | Voter          | Sujet                | Règle |
|------------------|----------------|----------------------|-------|
| `ORDER_VIEW`     | `OrderVoter`   | `OrderAccessSubject` | Propriétaire ou admin |
| `ORDER_CANCEL`   | `OrderVoter`   | `OrderAccessSubject` | Propriétaire uniquement |
| `USER_VIEW`      | `UserVoter`    | `UserAccessSubject`  | Soi-même ou admin |
| `PAYMENT_ACCESS` | `PaymentVoter` | `PaymentAccessSubject` | Propriétaire uniquement (pas de passe-droit admin) |
| `PRODUCT_ADMIN`  | `ProductVoter` | `null`               | Admin (brouillons, produits non publiés) |
| `MEDIA_LIST`     | `MediaVoter`   | `MediaListSubject`   | Public si `ownerType=product` ; sinon admin. Produit manquant ou non publié → `ProductNotFound` (404), comme `GET /products/:id`. |

### Contrôles déclaratifs (value resolvers)

| Resolver | Résout | Route / entrée |
|----------|--------|----------------|
| `OrderArgumentValueResolver` | `OrderResponse`, `OrderAccessSubject` | `{id}` ou `{orderId}` |
| `UserAccessSubjectValueResolver` | `UserAccessSubject` | `/users/{id}` |
| `PaymentAccessSubjectValueResolver` | `PaymentAccessSubject` | `{orderId}` ou JSON `orderId` |
| `MediaListArgumentValueResolver` | `ListMediaHttpRequest`, `MediaListSubject` | query `ownerType`, `ownerId` |

Certaines actions déclarent un argument sujet uniquement pour `#[IsGranted(subject: …)]` ; il n’est pas utilisé dans le corps du handler.

Un vote `MEDIA_LIST` refusé pour un anonyme devient **401** via `ApiExceptionSubscriber` (comme `GrantChecker::denyUnlessGrantedOrRequireAuthentication`).

## Domaine

Les règles de propriété métier vivent sur les agrégats (ex. `Order::belongsTo(CustomerId)`). Voters et handlers peuvent les réutiliser ; le domaine ne dépend jamais de Symfony Security.

## Erreurs HTTP

| Situation                         | Statut | `error.code`      |
|-----------------------------------|--------|-------------------|
| Non connecté sur une route protégée | 401  | `unauthenticated` |
| Connecté, pas le droit            | 403    | `forbidden`       |

`ApiExceptionSubscriber` mappe `AccessDeniedException` de Symfony en 401 si l’appelant n’est pas authentifié.

| Méthode | Quand l’utiliser |
|---------|------------------|
| `denyUnlessGranted($attribute, $subject?)` | La route a déjà `IS_AUTHENTICATED` — refus → **403** |
| `denyUnlessGrantedOrRequireAuthentication($attribute, $subject?)` | Route publique avec voter — anonyme → **401**, sinon **403** |
