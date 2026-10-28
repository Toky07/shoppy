# Modules

Shoppy est une boutique découpée en **contextes bornés**. Chaque module API vit sous `api/src/{Module}` et suit la même architecture hexagonale :

| Couche | Rôle |
|--------|------|
| `Domain` | Entités, value objects, exceptions, ports de persistance |
| `Application` | Commandes, requêtes, handlers, subscribers d’événements |
| `Infrastructure` | Doctrine, SMTP, Stripe, TCPDF, fichiers |
| `Presentation` | Contrôleurs HTTP, DTO de requête, voters |

Les modules **ne s’appellent pas entre eux** pour les effets de bord. Ils publient des événements sur le bus (`App\Shared\Infrastructure\Event\SymfonyEventBus`). Les abonnés d’autres modules réagissent.

Le front (`front/src/modules`) miroite ces contextes : un module Vue consomme l’API via un repository HTTP, sans connaître le domaine PHP.

Voir aussi [authorization.md](authorization.md), [email.md](email.md) et [stripe.md](stripe.md).

## Chaîne d’achat

```
Panier (Cart)
  → checkout / guest checkout
  → OrderPlaced
      ├─ Payment : crée un paiement pending
      ├─ Invoice : snapshot des lignes (produits + livraison)
      ├─ Address : mémorise l’adresse de livraison
      └─ Email   : confirmation de commande au client
  → PaymentCompleted  (Stripe, confirm local, ou admin « marquer payé »)
      ├─ Order   : statut paid
      ├─ Email   : confirmation de paiement
      └─ Invoice : émet FAC-YYYY-NNNN + InvoiceIssued
                    ├─ PDF TCPDF
                    ├─ mail client (PDF en pièce jointe)
                    └─ mail administrateur
```

Annulation : `OrderCancelled` annule le paiement pending et envoie un mail au client.

---

## Bus d’événements (`Shared`)

Événements d’intégration dans `App\Shared\Application\Event` :

| Événement | Émetteur | Abonnés |
|-----------|----------|---------|
| `OrderPlaced` | Cart (checkout) | Payment, Invoice (snapshot), Address, Order (mail client) |
| `PaymentCompleted` | Payment (complete / confirm / webhook Stripe / mark-paid) | Order (paid), Invoice (émission), Payment (mail payeur) |
| `InvoiceIssued` | Invoice | PDF, mails client et admin |
| `OrderCancelled` | Order | Payment (annule le pending), Order (mail client) |
| `OrderMarkedPaid` | Order (admin) | Payment (`CompletePaymentOnOrderMarkedPaid` → `PaymentCompleted`) |
| `UserRoleChanged` | User | Auth (révoque les sessions) |

`EmailRequested` (`App\Email\Domain\Event`) n’est **pas** un événement Shared : c’est le contrat interne du module Email. Les autres modules le publient via le `EventBus` ; `SendEmailOnEmailRequested` le livre.

---

## API — User

**Chemin** : `api/src/User`

Identité métier du compte : email, rôle (`customer` / `admin`), dates de création, vérification d’email et suppression logique.

Le mot de passe, les jetons et la session **n’appartiennent pas** à User : ils sont dans Auth. User est la fiche ; Auth est la porte.

**Domaine** : `User` (register, changer d’email, vérifier, supprimer, assigner un rôle). Un changement de rôle publie `UserRoleChanged`.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `POST` | `/users` | public | Inscription (délégué à Auth `RegisterAccountController`) |
| `GET` | `/users/{id}` | soi-même ou admin (`USER_VIEW`) | Fiche utilisateur |
| `PATCH` | `/users/{id}` | admin | Assigne `customer` ou `admin` |
| `GET` | `/admin/users` | admin | Liste des comptes |

---

## API — Auth

**Chemin** : `api/src/Auth`

Authentification et cycle de vie du compte : identifiants (`Credentials`), jetons d’accès (`AccessToken`, cookie `shoppy_session`), jetons à usage unique (`AccountToken` : reset mot de passe, vérification d’email, changement d’email, suppression).

`AccessTokenAuthenticator` construit l’`AuthenticatedUser` Symfony. `CurrentUser` expose l’id et `isAuthenticated()` aux autres modules. Détail des rôles et voters : [authorization.md](authorization.md).

`RevokeTokensOnRoleChanged` invalide toutes les sessions quand User publie `UserRoleChanged`.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `POST` | `/auth/login` | public | Session + cookie |
| `POST` | `/auth/logout` | authentifié | Révoque le jeton courant |
| `POST` | `/auth/logout-all` | authentifié | Révoque toutes les sessions |
| `POST` | `/auth/refresh` | cookie / jeton | Renouvelle la session |
| `GET` | `/auth/me` | authentifié | Profil courant |
| `POST` | `/auth/password-resets` | public | Demande de reset |
| `POST` | `/auth/password-resets/confirm` | public | Applique le nouveau mot de passe |
| `POST` | `/auth/email-verifications/request` | authentifié | Renvoie le mail de vérification |
| `POST` | `/auth/email-verifications` | public | Confirme l’email |
| `POST` | `/auth/password` | authentifié | Change le mot de passe (session courante) |
| `POST` | `/auth/email-changes` | authentifié | Demande de nouvel email |
| `POST` | `/auth/email-changes/confirm` | public | Confirme le nouvel email |
| `POST` | `/auth/account/deletion` | authentifié | Suppression du compte |

---

## API — Product

**Chemin** : `api/src/Product`

Catalogue : produits (nom, slug, SKU, prix, description, stock, catégorie, publication), **variantes** (SKU / stock / options propres), catégories, avis clients.

Le stock d’un produit sans variante est géré sur le produit. Avec variantes, le stock produit est la somme des variantes ; on ne le pose pas directement (`ProductStockManagedByVariants`).

Import CSV : `bin/console app:import-products` (`ImportProductsFromCsvCommandHandler`, fichier par défaut `api/fixtures/products.csv`). Le seed démo (`app:seed-demo`) s’en sert aussi.

SEO : `GET /sitemap.xml` et `GET /robots.txt`.

Avis : un client authentifié peut noter un produit et signaler un avis. L’admin liste, masque ou restaure.

**HTTP catalogue**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/products` | public (`includeDrafts` → admin) | Liste paginée, recherche, tri, filtres |
| `GET` | `/products/{id}` | public (brouillon → admin) | Fiche |
| `GET` | `/products/{id}/related` | public | Produits liés |
| `GET` | `/categories` | public | Catégories |
| `POST` | `/products` | admin | Création (variantes optionnelles) |
| `PATCH` | `/products/{id}` | admin | Mise à jour |
| `DELETE` | `/products/{id}` | admin | Suppression |
| `PUT` | `/products/{id}/stock` | admin | Stock (audit `stock_changed`) |
| `POST` | `/categories` | admin | Catégorie |

**HTTP avis**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/products/{id}/reviews` | public | Avis visibles |
| `POST` | `/products/{id}/reviews` | authentifié | Dépose un avis |
| `POST` | `/products/{id}/reviews/{reviewId}/report` | authentifié | Signale |
| `GET` | `/admin/reviews` | admin | Modération |
| `POST` | `/admin/reviews/{id}/hide` | admin | Masque |
| `POST` | `/admin/reviews/{id}/restore` | admin | Restaure |

Un changement de prix admin est journalisé (`price_changed` dans Audit).

---

## API — Media

**Chemin** : `api/src/Media`

Fichiers binaires rattachés à un **propriétaire**. Aujourd’hui le seul type d’owner est `product` (`MediaOwnerType`). Positionnable pour une galerie.

La liste publique n’expose que les médias d’un produit **publié** (`ProductMediaOwnerVisibility`). L’admin peut lister / uploader / réordonner / supprimer.

Le logo de facture **n’est pas** un média produit : il est stocké dans Invoice (branding).

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/media` | public si owner produit publié, sinon admin (`MEDIA_LIST`) | Liste par owner |
| `POST` | `/media` | admin | Upload |
| `PUT` | `/media/order` | admin | Réordonne |
| `DELETE` | `/media/{id}` | admin | Supprime |

---

## API — Cart

**Chemin** : `api/src/Cart`

Panier d’un client authentifié : lignes (produit + variante optionnelle + quantité), version pour concurrence, **réservation de stock** (`reserved_stock` côté catalogue).

Le checkout **place la commande** : il lit le panier, crée l’`Order`, vide le panier, publie `OrderPlaced` (lignes + libellé / montant de livraison). C’est Cart, pas Order, qui orchestre le passage caisse, pour que le stock réservé et le panier restent cohérents.

**Guest checkout** : si aucun utilisateur n’est connecté, `GuestCheckoutCommandHandler` crée un compte à partir de l’email, pose une session, et commande les `items` du payload (pas un panier persisté).

`POST /orders` (module Order) réutilise le même `CheckoutCartCommandHandler` pour un client déjà connecté.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/cart` | authentifié | Panier + stock restant |
| `POST` | `/cart/items` | authentifié | Ajoute une ligne |
| `PUT` | `/cart/items/{productId}` | authentifié | Quantité |
| `DELETE` | `/cart/items/{productId}` | authentifié | Retire |
| `DELETE` | `/cart` | authentifié | Vide |
| `POST` | `/cart/merge` | authentifié | Fusionne un panier anonyme (après login) |
| `POST` | `/cart/checkout` | public si guest (email + items), sinon session | Place la commande |

---

## API — Order

**Chemin** : `api/src/Order`

Commande passée : client, lignes figées (prix au moment du checkout), adresses, mode de livraison, email client, statut `pending` → `paid` ou `cancelled`.

L’admin peut **marquer payée** une commande pending (`OrderMarkedPaid`). Payment complète alors le paiement pending et republie `PaymentCompleted`, ce qui enchaîne facture et mails comme un vrai paiement.

`MarkOrderPaidOnPaymentCompleted` passe la commande en `paid` quand le paiement aboutit (Stripe, confirm local, ou mark-paid).

Mails client : confirmation à `OrderPlaced`, annulation à `OrderCancelled` (`EmailCustomerAboutOrder` → `EmailRequested`).

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `POST` | `/orders` | authentifié | Checkout du panier connecté |
| `GET` | `/orders` | authentifié | Commandes du client |
| `GET` | `/orders/{id}` | propriétaire ou admin (`ORDER_VIEW`) | Détail |
| `POST` | `/orders/{id}/cancel` | propriétaire (`ORDER_CANCEL`) | Annule si pending |
| `POST` | `/orders/{id}/mark-paid` | admin | Paiement manuel + `OrderMarkedPaid` |
| `GET` | `/admin/orders` | admin | Toutes les commandes |
| `GET` | `/admin/dashboard` | admin | Indicateurs (CA, volumes) |

---

## API — Payment

**Chemin** : `api/src/Payment`

Un paiement **pending** est créé à `OrderPlaced` (`CreatePaymentOnOrderPlaced`). Statuts : `pending`, `completed`, `cancelled`.

`PAYMENT_PROVIDER=local` : le front appelle `POST /payments/complete` ou `POST /payments/confirm` (dev / tests). `PAYMENT_PROVIDER=stripe` : `POST /payments/checkout` crée une session Stripe Checkout ; `POST /payments/webhooks/stripe` (`checkout.session.completed`) marque completed. Production : voir [stripe.md](stripe.md). `PAYMENT_PROVIDER=local` est refusé en prod.

`CompletePaymentOnOrderMarkedPaid` complète le pending admin et **dispatch `PaymentCompleted`** (facture + mail). `CancelPaymentOnOrderCancelled` annule le pending. `EmailPayerOnPaymentCompleted` mail le payeur.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/payments/by-order/{orderId}` | propriétaire (`PAYMENT_ACCESS`) | Statut |
| `POST` | `/payments/checkout` | propriétaire | Session Stripe (si provider stripe) |
| `POST` | `/payments/complete` | propriétaire | Complete local |
| `POST` | `/payments/confirm` | propriétaire | Confirm retour Stripe / local |
| `POST` | `/payments/webhooks/stripe` | Stripe (signature) | `checkout.session.completed` |

---

## API — Invoice

**Chemin** : `api/src/Invoice`

Facture **émise une fois** le paiement confirmé. Numérotation `FAC-YYYY-NNNN`. Snapshot des lignes à `OrderPlaced` (produits + ligne « Livraison {méthode} ») pour ne pas dépendre du catalogue ensuite. À `PaymentCompleted`, création si aucune facture n’existe déjà pour la commande : montant, lignes, logo branding courant, puis `InvoiceIssued`.

`GenerateInvoicePdfOnInvoiceIssued` génère un PDF TCPDF (logo + lignes) et l’attache à la facture. `EmailCustomerOnInvoiceIssued` / `EmailAdminOnInvoiceIssued` envoient des mails HTML + texte, PDF en pièce jointe côté client.

Le logo n’est pas recalculé à chaque PDF : il est **figé** sur la facture à l’émission. L’admin pose le branding via upload.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/invoices/by-order/{orderId}/pdf` | propriétaire ou admin (`ORDER_VIEW`) | PDF inline |
| `GET` | `/invoices/branding` | admin | Logo courant |
| `POST` | `/invoices/branding/logo` | admin | Upload du logo |

---

## API — Email

**Chemin** : `api/src/Email`

Port `Mailer` uniquement. Personne n’envoie un SMTP directement. Un module construit `EmailRequested` (destinataire, sujet, corps HTML/texte, pièces jointes) ; `SendEmailOnEmailRequested` appelle `SendEmailCommandHandler` → `SymfonyMailer`.

Sans `MAILER_DSN` réel, rien ne part (`null://null`). En local, Mailpit (`smtp://mailer:1025`). Production : [email.md](email.md).

En test, `InMemoryMailer` empile les messages (boîte **statique** pour survivre au reboot du kernel PHPUnit / KernelBrowser).

Expéditeur : `MAILER_FROM`. Liens dans les mails : `FRONTEND_BASE_URL`.

---

## API — Address

**Chemin** : `api/src/Address`

Carnet d’adresses du client. Pas de POST de création dédié : `RememberAddressOnOrderPlaced` enregistre l’adresse de **livraison** au checkout (capacité limitée, `AddressBookFull` ignoré silencieusement si plein).

Le client liste, choisit une adresse par défaut, ou supprime.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/addresses` | authentifié | Carnet |
| `POST` | `/addresses/{addressId}/default` | authentifié | Adresse par défaut |
| `DELETE` | `/addresses/{addressId}` | authentifié | Supprime |

---

## API — Favorite

**Chemin** : `api/src/Favorite`

Liste de favoris par client (`FavoriteList` : ids produits, sans duplication). Fusion après login (`POST /favorites/merge`) comme le panier.

**HTTP**

| Méthode | Route | Accès | Effet |
|---------|-------|-------|--------|
| `GET` | `/favorites` | authentifié | Liste |
| `POST` | `/favorites` | authentifié | Ajoute |
| `DELETE` | `/favorites/{productId}` | authentifié | Retire |
| `DELETE` | `/favorites` | authentifié | Vide |
| `POST` | `/favorites/merge` | authentifié | Fusionne une liste anonyme |

---

## API — Newsletter

**Chemin** : `api/src/Newsletter`

Inscription email (`NewsletterSubscriber`). Public, idempotent : réinscrire la même adresse ne casse pas. Pas de double opt-in dans ce module.

**HTTP** : `POST /newsletter`.

---

## API — Audit

**Chemin** : `api/src/Audit`

Journal append-only des actions sensibles, via `RecordingAuditLogger`. Pas d’événements Shared : les modules appellent le logger dans leurs handlers. Les métadonnées incluent un `label` figé à l’écriture (nom du produit, email du compte, noms des articles de la commande) pour que le journal reste lisible sans UUID.

| Action | Sujet typique | `label` |
|--------|----------------|---------|
| `role_changed` | User | email du compte |
| `price_changed` | Product | nom du produit |
| `stock_changed` | Product | nom du produit |
| `payment_changed` | Order (mark-paid) | articles de la commande |

**HTTP** : `GET /admin/audit` (admin, paginé).

---

## API — Shared

**Chemin** : `api/src/Shared`

Noyau transversal, **pas** un métier boutique :

- bus d’événements et contrats `OrderPlaced`, `PaymentCompleted`, …
- horloge (`Clock` / `SystemClock`)
- transactions Doctrine
- `GET /health` (API + Postgres)
- CORS, mapping d’exceptions JSON, rate limiting
- cache HTTP public (`CachedJsonResponse`) pour le catalogue
- garde-fous prod (`ProductionSecretGuard` : `APP_SECRET`, pas de seed démo, pas de `PAYMENT_PROVIDER=local`)
- commande `app:seed-demo`

---

## Front

Documentation détaillée : [`front/docs/`](../../front/docs/README.md) (architecture, HTTP, auth, UI, tests).

Modules sous `front/src/modules`. Couches typiques : `domain`, `application` (ports / keys d’injection), `data` (HTTP), `ui`, `testing` (fakes).

### `auth`

Connexion, inscription, mot de passe oublié, reset, vérification / confirmation d’email, page compte (`AccountPage` : identité, sécurité, préférences, activité). Cookie / présence de session (`sessionPresenceStore`, `restoreAuthSession`). Login admin séparé dans `admin`.

Routes : `/login`, `/register`, `/forgot-password`, `/reset-password`, `/verify-email`, `/confirm-email`, `/account`.

### `account`

Carnet d’adresses (`AddressBookPanel`) branché sur `/addresses`. Embed dans la page compte Auth, pas une route dédiée.

### `catalog`

Vitrine : liste, fiche (slug), favoris, variantes, avis, médias, prix, stock. Repositories public (`ProductHttpRepository`) et admin (`AdminCatalogHttpRepository`).

Routes : `/`, `/products/:slug`, `/favorites`.

### `cart`

Panier, lignes, résumé, formulaires d’adresse / livraison, checkout (connecté ou invité). Merge au login.

Route : `/cart`.

### `order`

Historique et détail. Panneau paiement / facture PDF une fois la commande passée. Annulation côté client.

Routes : `/orders`, `/orders/:id` (`requiresAuth`).

### `payment`

Repository HTTP checkout / complete / confirm / statut. Pas de page autonome : le flux vit dans le détail commande (`OrderCheckoutPanel`). Libellés de statut.

### `newsletter`

Formulaire d’inscription (layout boutique), `POST /newsletter`.

### `legal`

Pages statiques CGV, confidentialité, mentions, retours (`LegalPage` + `legalDocuments`).

Routes : `/cgv`, `/confidentialite`, `/mentions`, `/retours`.

### `admin`

Back-office (`AdminLayout`, `requiresAdmin`) : tableau de bord, commandes (détail + marquer payé), catalogue (création / édition / galerie média), utilisateurs (rôles), modération d’avis, journal d’audit. Login dédié `/admin/login`.

Routes : `/admin`, `/admin/orders`, `/admin/orders/:id`, `/admin/products`, `/admin/products/new`, `/admin/products/:id`, `/admin/users`, `/admin/reviews`, `/admin/audit`.

---

## Correspondance API ↔ front

| API | Front |
|-----|--------|
| User + Auth | `auth`, `admin` (users) |
| Product + Media + Favorite | `catalog`, `admin` (products / reviews) |
| Cart | `cart` |
| Order | `order`, `admin` (orders / dashboard) |
| Payment | `payment` (embarqué dans `order`) |
| Invoice | PDF depuis le détail commande ; logo dans l’admin produit / branding API |
| Address | `account` |
| Newsletter | `newsletter` |
| Audit | `admin` (audit) |
| Email | aucun module UI (envoi serveur) |
| Shared | `front/src/shared` (layout, HTTP, erreurs) |

## Règle d’ajout d’un module

1. Nouveau dossier `api/src/{Nom}` (hexagone) + tests Pest dans `api/tests`.
2. Effets de bord vers d’autres contextes : événement Shared (ou `EmailRequested` pour un mail), jamais un appel de handler étranger.
3. Routes et voters dans Presentation ; persistance Doctrine isolée.
4. Côté front, un module du même nom (repository HTTP + pages) s’il y a une UI.
5. Documenter ici le rôle, les événements et les routes.
