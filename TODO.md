# TODO

Audit du 22 septembre 2026. Cocher au fur et à mesure.

Déjà en place : inscription / connexion / rôles, sécurité du compte (réinitialisation, vérification d’email, changement de mot de passe et d’email, déconnexion de toutes les sessions, suppression anonymisée, refus de supprimer le dernier admin), catalogue paginé (recherche, tri, slug, stock, catégories, filtres prix et « en stock seulement », favoris locaux), panier authentifié, commande avec prix serveur, annulation qui restaure le stock, Stripe (session + webhook signé), admin produits / commandes / utilisateurs, emails transactionnels, tests Pest et Vitest.

## Fonctionnalités manquantes

### Compte

- [x] Réinitialisation du mot de passe (demande, email, token à usage unique, expiration)
- [x] Vérification de l’adresse email à l’inscription
- [x] Changement de mot de passe et d’email depuis le compte
- [x] Suppression / anonymisation du compte
- [x] Déconnexion de toutes les sessions (le logout d’un seul appareil ne révoque toujours qu’un token)
- [ ] Favoris rattachés au compte (aujourd’hui `localStorage`, perdus en changeant d’appareil)

### Catalogue

- [x] Catégories (navigation, filtre, rattachement produit)
- [ ] Variantes (taille, couleur) et SKU métier (l’UI affiche un UUID tronqué)
- [x] Filtres prix et « en stock seulement »
- [ ] Produits associés sur la fiche
- [ ] Statut publié / brouillon (tout produit créé est visible)
- [ ] Upload, ordre et suppression d’images dans le formulaire admin — l’API `POST/DELETE /media` existe, l’UI ne l’appelle pas
- [ ] Mise à jour à l’import CSV (aujourd’hui une ligne dont le nom existe est ignorée)
- [ ] Avis clients

### Achat, livraison, commande

- [ ] Adresses de livraison et de facturation
- [ ] Modes de livraison et frais (le libellé « Livraison : Offerte » est en dur)
- [ ] TVA / taxes (le total est une somme de lignes, affiché « TTC » sans calcul)
- [ ] Codes promo
- [ ] Commande invité, ou fusion du panier après connexion
- [ ] Réserver le stock entre panier et paiement (le décrément n’a lieu qu’à la création de la commande, sans verrou)
- [ ] Rendre atomiques stock + création de commande (si le second produit échoue, le premier est déjà décrémenté ; pas de transaction)
- [ ] Empêcher un double checkout du même panier (deux `POST /cart/checkout` parallèles créent deux commandes)
- [ ] Clarifier le parcours : « Payer ma commande » sur le panier crée seulement la commande ; le paiement est sur `/orders/:id`
- [ ] Photo produit dans le panier (icône générique aujourd’hui)
- [ ] Retour visuel Stripe (`?payment=success` / `?payment=cancel` sont dans l’URL, la page ne s’en sert pas)
- [x] Emails de confirmation, de paiement et d’annulation via `EmailRequested` (pièces jointes supportées ; la confirmation joint `recu.txt`)
- [ ] Email d’expédition, quand le suivi existera
- [ ] Facture PDF
- [ ] Remboursement (statuts paiement : pending / completed / cancelled seulement)
- [ ] Suivi d’expédition (statuts commande : pending / paid / cancelled)
- [ ] Filtres d’historique (statut, date) côté client et admin
- [ ] Annulation admin, et affichage de l’email client (aujourd’hui un UUID)

### Paiement

- [ ] Aligner « Marquer comme payée » avec le `Payment` (la commande passe `paid`, le paiement reste `pending`, et un checkout Stripe reste possible)
- [ ] Refuser un nouveau checkout si la commande n’est plus `pending`
- [ ] À l’annulation, invalider la session Stripe ouverte (sinon le webhook peut encaisser une commande déjà annulée)
- [ ] Comparer le montant Stripe au montant de la commande dans le webhook
- [ ] Index sur `payments.provider_reference` (lookup webhook)

### Admin et boutique

- [ ] Tableau de bord (CA, commandes du jour, stock bas) — l’accueil admin ne fait que des liens
- [ ] Journal d’audit (qui a changé un rôle, un prix, un stock, un paiement)
- [ ] Garde-fou « dernier admin » à la rétrogradation (la suppression du dernier admin est déjà refusée)
- [ ] Guards router `auth` / `admin` (aujourd’hui `AuthRequiredPanel` et `AdminLayout` seulement)
- [ ] SEO par produit (title, meta, Open Graph, `sitemap.xml`, `robots.txt`)
- [ ] Pages légales (CGV, confidentialité, mentions, retours) — le footer a des libellés non cliquables
- [ ] Bandeau cookies / base RGPD
- [ ] Newsletter du footer branchée, ou retirée
- [ ] i18n (textes français en dur, devise EUR fixe)

### Qualité

- [ ] Parcours e2e (auth, achat, admin) — Playwright ne couvre qu’un smoke catalogue, et pas en CI
- [ ] Lint front dans la CI
- [ ] Retirer Pinia ou l’utiliser (installé, aucun store)
- [ ] Retirer `vite-plugin-vue-devtools` du build de production

## Optimisations

- [x] Batcher les médias du catalogue : `ProductResponseFactory` charge les images d’une page en une requête
- [x] Charger les favoris en une requête (`GET /products?ids=`)
- [ ] Dérivés d’images (vignette, carte, fiche) au lieu du fichier original ; `srcset` côté front
- [ ] Cache court ou ETag sur `GET /products` et `GET /products/:slug`
- [x] Index sur `products.price_cents`, `created_at`, `name` et `category_id` (la recherche `LIKE %…%` ne peut toujours pas utiliser un index B-tree)
- [ ] Remplacer SQLite en production (un fichier, pas de concurrence réelle ni de réplication)
- [ ] Redis pour le cache applicatif (commenté dans `cache.yaml`, non déployé)
- [ ] TTL de token plus court + refresh, et purge des tokens expirés (`access_tokens` n’a pas d’index sur `user_id`)
- [ ] Éviter le double lookup token (`RequireSelfOrAdmin` ré-authentifie)
- [ ] Un seul flush dans `DoctrineCartRepository::save()`
- [ ] Self-host des polices (Google Fonts bloque le premier rendu)
- [ ] Healthcheck dédié (`GET /health`) au lieu de `GET /products`

## Sécurité

### Critique

- [ ] **`POST /payments/complete` marque toujours le paiement comme réussi.** Le handler injecte `LocalPaymentGateway`, qui accepte toute charge, quel que soit `PAYMENT_PROVIDER`. Un client authentifié peut payer une commande sans encaissement. N’autoriser cet endpoint que si le provider effectif est `local`, et refuser `local` en production.
- [ ] Interdire au client de choisir le provider (`StartCheckout` prend `provider` dans le body). Forcer le provider configuré côté serveur.
- [ ] Rate limit sur `POST /auth/login`, `POST /users`, checkout et webhook
- [ ] Ne plus stocker le Bearer dans `localStorage` (`shoppy.session`) — cookie `httpOnly` + `SameSite`, ou équivalent non lisible par le JS
- [ ] Valider le contenu réel des uploads (magic bytes), pas `getClientMimeType()`
- [ ] Interdire ou assainir les SVG (`image/svg+xml` est autorisé et servi depuis la même origine)
- [ ] Restreindre `successUrl` / `cancelUrl` à des origines autorisées avant de les passer à Stripe

### Auth et comptes

- [ ] Politique de mot de passe au-delà de 8 caractères
- [ ] Même temps de réponse login si l’email n’existe pas (`password_verify` n’est pas appelé)
- [ ] Ne pas répondre `409 email_already_registered` de façon énumérable, ou l’assumer explicitement
- [x] Révoquer les tokens au changement de mot de passe, à la réinitialisation et à la confirmation d’un nouvel email
- [ ] Révoquer les tokens au changement de rôle
- [ ] Plafonner le nombre de sessions par utilisateur
- [ ] Revalider `/auth/me` au chargement (rôle et expiration viennent du `localStorage`)
- [ ] Centraliser l’authentification (subscriber) : chaque contrôleur appelle Bearer / `RequireAdmin` à la main
- [ ] Ne pas seeder `admin@shoppy.test` / `password123` en production

### Fichiers et HTTP

- [ ] `mkdir(..., 0777)` sur les uploads (`FilesystemMediaStorage`) — passer à `0755` / `0644`
- [ ] Headers qui manquent : `Content-Security-Policy`, `Strict-Transport-Security`, `Permissions-Policy`
- [ ] Répéter les headers de sécurité dans les `location` nginx qui définissent leur propre `add_header` (`/assets/`, `/uploads/`, `/media/`), sinon nginx n’hérite pas ceux du `server`
- [ ] Servir `/uploads` sans exécution, avec le `Content-Type` détecté côté serveur
- [ ] HTTPS en production (`auto_https off`, nginx en clair sur le port 80)
- [ ] Pas de `APP_SECRET` par défaut `change-me-in-production` ; sortir `api/.env.dev` des secrets versionnés (ou le documenter comme secret de dev uniquement)
- [ ] CORS explicite si l’API n’est plus same-origin
- [ ] Endpoint `GET /media` : ne pas lister les médias d’un owner sans contrôle d’accès si d’autres owners que `product` apparaissent

### Cohérence métier

- [ ] Verrou pessimiste (ou version) sur le stock au moment du décrément
- [ ] Transaction unique : décrément, commande, paiement pending
- [ ] Après annulation, ignorer un webhook Stripe tardif au lieu de compléter le paiement d’une commande annulée
