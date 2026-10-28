# Modules front

Chaque dossier `front/src/modules/{nom}` est un contexte UI. Il parle à l’API via un port (`application/*Repository.ts`) et un adaptateur HTTP (`data/*HttpRepository.ts`).

Correspondance API : [`api/docs/modules.md`](../../api/docs/modules.md).

## `auth`

Compte et session. Pas le carnet d’adresses (voir `account`).

**Domaine** : `User`, `Session` (`{ user }`).

**Port** : `AuthRepository` (login, register, me, logout, reset / verify / change email, logout-all, delete). `UserDirectory` pour la liste admin (`GET /admin/users`, `PATCH /users/{id}`).

**État** : `createAuthSession` + `sessionPresenceStore`.

**Pages**

| Route | Nom | Accès |
|-------|-----|--------|
| `/login` | `login` | public |
| `/register` | `register` | public |
| `/forgot-password` | `forgot-password` | public |
| `/reset-password` | `reset-password` | public (`?token=`) |
| `/verify-email` | `verify-email` | public (`?token=`) |
| `/confirm-email` | `confirm-email` | public (`?token=`) |
| `/account` | `account` | `requiresAuth` |

`AccountPage` assemble identité, activité, préférences, `AddressBookPanel` (`account`), sécurité.

Use cases : `login`, `logout`, `register`, `restoreAuthSession`, `accountActions`.

## `account`

Carnet d’adresses du client. Pas de route propre : panneau dans `/account` et préremplissage checkout.

**Port** : `AddressBookRepository` → `GET /addresses`, `POST /addresses/{id}/default`, `DELETE /addresses/{id}`.

**État** : `createAddressBookState` (no-op si anonyme ; `refresh` après checkout).

Les adresses naissent au **checkout** (l’API les mémorise), pas via un formulaire « ajouter » dédié.

## `catalog`

Vitrine et, via un second port, CRUD admin catalogue / avis.

**Ports**

- `CatalogRepository` (`ProductHttpRepository`) : liste (page, `q`, sort, prix, stock, catégorie, `ids=`), fiche, related, catégories, avis, signalement, dépôt d’avis.
- `FavoriteRepository` : liste / add / remove / clear / merge.
- `AdminCatalogRepository` : create / update / stock / delete, galerie `POST /media` (FormData `ownerType=product`), reorder, hide/restore avis.

**État** : `createFavoritesState` (localStorage anonyme + merge). `useProductList` / `useCatalogView` lisent la query string (page, recherche, filtres).

**Pages boutique**

| Route | Nom | Rôle |
|-------|-----|------|
| `/` | `catalog` | Grille, hero, toolbar |
| `/products/:slug` | `product` | Fiche (variantes, avis, related, SEO) |
| `/favorites` | `favorites` | Liste d’ids → `listByIds` |

Composants notables : `ProductCard`, `VariantPicker`, `FavoriteButton`, `ProductReviews`, `AddToCartForm` (module `cart`).

## `cart`

Panier + checkout.

**Port** : `CartRepository` → `/cart`, `/cart/items`, `/cart/merge`, `/cart/checkout`.

**État** : `createCartState` (API si session, `shoppy.guestCart` sinon). `itemCount` alimente le badge header.

**Page** : `/cart` (`cart`).

Checkout : adresses (carnet si connecté), facturation = livraison ou distincte, `standard` / `express`, email si invité. Succès → `authSession.set` si `result.user` → `/orders/:id`.

## `order`

Commandes client. Le paiement **s’affiche ici**, le client HTTP est le module `payment`.

**Port** : `OrderRepository` → `GET /orders`, `GET /orders/{id}`, `POST …/cancel`, plus `listAll` / `markPaid` pour l’admin.

**Pages**

| Route | Nom | Accès |
|-------|-----|--------|
| `/orders` | `orders` | `requiresAuth` |
| `/orders/:id` | `order` | `requiresAuth` |

`OrderDetailPage` : `useOrder` + `usePaymentByOrder`. Payer → `startCheckout`. Retour Stripe `?payment=success` → `confirm`. `?payment=cancel` → notice. Annuler si `pending`.

PDF facture : **non branché** (l’API le sert déjà).

## `payment`

Pas de page. Repository + `usePaymentByOrder` + libellés de statut.

**Port** : `PaymentRepository`

| Méthode | API |
|---------|-----|
| `getByOrder` | `GET /payments/by-order/{orderId}` |
| `startCheckout` | `POST /payments/checkout` (`orderId`, `successUrl`, `cancelUrl`) |
| `confirm` | `POST /payments/confirm` |
| `complete` | `POST /payments/complete` |

`PaymentCheckout` : `redirectUrl` (Stripe) ou `completedImmediately` (provider `local`). Les URLs de retour sont l’origine front + `/orders/{id}?payment=success|cancel`.

Provider et clés : [`api/docs/stripe.md`](../../api/docs/stripe.md). Le front ne voit jamais `STRIPE_SECRET_KEY`.

## `newsletter`

**Port** : `NewsletterRepository` → `POST /newsletter`.

Composant `NewsletterForm` dans le footer (`AppLayout`). Pas de route. Idempotent côté API.

## `legal`

Pages statiques, **sans API**. `legalDocuments.ts` : CGV, confidentialité, mentions, retours.

| Route | Nom |
|-------|-----|
| `/cgv` | `legal-cgv` |
| `/confidentialite` | `legal-confidentialite` |
| `/mentions` | `legal-mentions` |
| `/retours` | `legal-retours` |

Une seule `LegalPage.vue` selon le nom de route. Meta SEO par document.

## `admin`

Back-office. Layout `AdminLayout`, `meta.requiresAdmin` sur `/admin`. Nav : `adminNav.ts`.

**Ports propres** : `AdminDashboardRepository` (`GET /admin/dashboard`), `AuditLogRepository` (`GET /admin/audit`). Réutilise `OrderRepository.listAll` / `markPaid`, `AdminCatalogRepository`, `UserDirectory`.

| Route | Nom | Rôle |
|-------|-----|------|
| `/admin/login` | `admin-login` | Hors layout, public |
| `/admin` | `admin` | CA, commandes du jour, ruptures |
| `/admin/orders` | `admin-orders` | Toutes les commandes |
| `/admin/orders/:id` | `admin-order` | Détail + marquer payée |
| `/admin/products` | `admin-products` | Catalogue (brouillons compris) |
| `/admin/products/new` | `admin-product-new` | Création |
| `/admin/products/:id` | `admin-product-edit` | Édition, variantes, galerie |
| `/admin/reviews` | `admin-reviews` | Masquer / restaurer |
| `/admin/users` | `admin-users` | Rôle `customer` / `admin` |
| `/admin/audit` | `admin-audit` | Journal `role_changed`, `price_changed`, `stock_changed`, `payment_changed` |

Marquer payée déclenche côté API `OrderMarkedPaid` → paiement completed → facture + mails, comme un paiement réel.

## `shared` (pas un module métier)

Layout boutique, HTTP, i18n, thème, consentement, SEO, argent, helpers. Voir [architecture.md](architecture.md) et [ui.md](ui.md).

## Correspondance

| Front | API |
|-------|-----|
| `auth` | Auth + User |
| `account` | Address |
| `catalog` | Product, Media, Favorite |
| `cart` | Cart |
| `order` | Order (+ Invoice PDF à brancher) |
| `payment` | Payment |
| `newsletter` | Newsletter |
| `admin` | Order dashboard, User, Product admin, Audit, Media |
| `legal` | — (contenu front) |
| — | Email (envoi serveur uniquement) |

## Ajouter un module

1. Dossiers `domain` / `application` / `data` / `testing` / `ui`.
2. Port + `InjectionKey` + fake.
3. Brancher dans `app/dependencies.ts` et `main.ts` (`provide`).
4. Ajouter le `provide` dans `renderApp`.
5. Routes dans `router/routes.ts` (`meta` auth si besoin).
6. Catalogues i18n `fr` + `en`.
7. Documenter ici.
