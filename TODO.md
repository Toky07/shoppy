# TODO

Audit du 26 octobre 2026. Cocher au fur et à mesure.

Déjà en place : inscription / connexion (cookie `httpOnly`), rôles, sécurité du compte (reset, vérif email, sessions, anonymisation), catalogue paginé (recherche, tri, catégories, filtres, variantes, avis, favoris locaux), panier auth + invité (fusion à la connexion, photos produit), commande avec prix serveur, livraison Standard / Express, Stripe + webhook signé, admin produits / commandes / utilisateurs, emails transactionnels, Postgres, tests Pest et Vitest.

Récent : avatar seul dans le header, filtres décollés sur mobile, scroll en haut à la navigation, swipe tactile du carrousel, photos dans le panier.

## Fonctionnalités

### Compte

- [ ] Favoris rattachés au compte (aujourd’hui `localStorage` `shoppy.favorites`, perdus en changeant d’appareil)
- [ ] Checkout invité réel (le panier invité existe, la commande exige encore une connexion)
- [ ] Carnet d’adresses réutilisable (saisie uniquement au checkout)

### Catalogue

- [ ] Modération des avis côté admin (pas d’écran, pas de signalement)
- [ ] SEO par produit : title, meta, Open Graph, `sitemap.xml`, `robots.txt` (seul `index.html` a une description statique)

### Achat, livraison, commande

- [ ] TVA / taxes (le total est une somme de lignes, affiché « TTC » sans calcul)
- [ ] Codes promo
- [ ] Retour visuel Stripe (`?payment=success` / `?payment=cancel` sont dans l’URL, `OrderDetailPage` ne s’en sert pas)
- [ ] Statuts d’expédition et suivi colis (commande : `pending` / `paid` / `cancelled` seulement)
- [ ] Email d’expédition, quand le suivi existera
- [ ] Facture PDF (la confirmation joint encore `recu.txt`)
- [ ] Remboursement (paiement : `pending` / `completed` / `cancelled` seulement)
- [ ] Filtres d’historique (statut, date) côté client et admin
- [ ] Annulation admin (le détail admin ne propose que « Marquer comme payée »)
- [ ] Afficher l’email client sur la fiche commande admin (la liste l’a, le détail affiche encore l’UUID)

### Admin et boutique

- [ ] Tableau de bord (CA, commandes du jour, stock bas) — l’accueil admin ne fait que des liens
- [ ] Journal d’audit (qui a changé un rôle, un prix, un stock, un paiement)
- [ ] Pages légales (CGV, confidentialité, mentions, retours) — le footer a des libellés non cliquables
- [ ] Bandeau cookies / base RGPD
- [ ] Newsletter du footer branchée, ou retirée (`@submit.prevent` sans envoi)
- [ ] i18n (textes français en dur, devise EUR fixe)
- [ ] Titre de page dynamique sur la fiche produit (seul l’admin change `document.title`)

## Sécurité

### Haute

- [x] `GET /media?ownerType=product&ownerId=` liste les images d’un produit même s’il est en brouillon (`MediaVoter` autorise tout owner `product`, alors que `GET /products/:id` renvoie 404)

### Moyenne

- [x] Empêcher de rétrograder le dernier admin (`AssignUserRoleCommandHandler` ; la suppression du dernier admin est déjà refusée)
- [x] Aligner la politique de mot de passe : 8–20 caractères en code, le message parle de 12–4096 (`PlainPassword`, `InvalidPassword`)
- [x] Rate limit sur reset / vérif email / confirm token (aujourd’hui seulement login, register, checkout, webhook)
- [x] Rate limit et garde `claimForCheckout` aussi sur `POST /orders` (l’UI passe par `/cart/checkout`, l’API directe reste ouverte)
- [x] Filtrer les URLs d’images du CSV (SSRF possible via `StreamHttpDownloader` ; le chemin import contourne la détection MIME de `POST /media`)
- [x] Tokens de reset / vérif dans `?token=` (historique, analytics, Referer)

### Basse

- [x] Guards router `auth` / `admin` (aujourd’hui `AuthRequiredPanel` et `AdminLayout` seulement ; l’API reste la source de vérité)
- [x] TTL de token plus court + refresh (sessions à 7 jours, pas de rotation)
- [x] Rate limiter fichier : échoue ouvert si I/O KO, un compteur par replica
- [x] Purge des tokens expirés (`access_tokens` n’a pas d’index sur `user_id`)

## Optimisations

### API

- [x] Batcher le `GET /cart` : une ligne = produit + variantes + médias + 2e lookup catalogue + SQL de réservation (`GetCartQueryHandler`, `Cart\ProductCatalog`, `AvailableStock`)
- [x] Alléger la fusion du panier invité (chaque ligne rejoue add + stock)
- [x] JOIN FETCH des lignes de commande sur les listes (`DoctrineOrderRepository` : N+1 items)
- [x] Paginer les avis et batcher les auteurs (`ListProductReviewsQueryHandler` charge tout ; `UserReviewAuthors` fait un `findById` par avis)
- [x] Index sur `cart_items.product_id` (lookup `reservedQuantity`)
- [x] Un seul `flush()` dans `DoctrineCartRepository::save()`
- [x] Cache court ou ETag sur `GET /products` et `GET /products/:slug`
- [x] Redis pour le cache applicatif (commenté dans `cache.yaml`, non déployé)
- [x] Healthcheck dédié (`GET /health`) au lieu de `GET /products`

### Front et médias

- [x] Dérivés d’images (vignette, carte, fiche) au lieu du fichier original ; `srcset` dans `AppImage`
- [ ] Self-host des polices (Google Fonts dans `index.html`, autorisé par la CSP)
- [ ] Retirer Pinia ou l’utiliser (`createPinia()` dans `main.ts`, aucun store)
- [ ] Retirer `vite-plugin-vue-devtools` du build de production (`vite.config.ts`)

## Qualité

- [ ] Parcours e2e (auth, achat, admin) — Playwright ne couvre qu’un smoke catalogue, et pas en CI
- [ ] Lint front dans la CI (`npm run lint` existe, le workflow ne le lance pas)
