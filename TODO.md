# TODO

Liste de ce qui reste à faire, des optimisations, et des points de sécurité. Cocher au fur et à mesure.

## Fonctionnalités

### Admin
- [ ] Gérer les images produit dans le formulaire admin (upload, ordre, suppression) — l’API Media existe déjà (`POST/DELETE /media`)
- [ ] Lister / rechercher les utilisateurs (email, rôle) au lieu de coller un UUID
- [ ] Aligner « Marquer comme payée » avec le paiement : aujourd’hui la commande passe `paid` sans compléter le `Payment`

### Achat
- [ ] Clarifier le checkout : le bouton panier « Payer ma commande » crée seulement la commande ; le paiement réel est sur `/orders/:id`
- [ ] Afficher un retour visuel après Stripe (`?payment=success` / `?payment=cancel`)
- [ ] Afficher la photo produit dans le panier (aujourd’hui une icône générique)
- [ ] Réserver le stock au checkout (ou verrouiller) pour éviter une course entre deux commandes concurrentes

### Front
- [ ] Ajouter des guards router (`auth` / `admin`) au lieu de seulement `AuthRequiredPanel` et `AdminGate`
- [ ] Couvrir les parcours e2e (auth, achat, admin) — Playwright n’a qu’un smoke test catalogue
- [ ] Décider de Pinia : l’utiliser vraiment, ou le retirer (installé mais aucun store)

### Technique
- [ ] Retirer `SvgProductImageWriter` s’il n’est plus utilisé (remplacé par Media + import CSV)
- [ ] README racine (lancer API + front, seed, variables d’env Stripe / `PAYMENT_PROVIDER`)
- [ ] Étendre les owner types Media au-delà de `product` si d’autres entités en ont besoin

## Optimisations

- [ ] Paginer / cacher les listes lourdes (produits, commandes admin) côté serveur si le volume grandit
- [ ] Éviter N+1 : `ProductResponseFactory` charge les médias par produit ; batcher `ListMediaByOwner` sur une page catalogue
- [ ] Images : tailles dérivées (thumb / card / détail) au lieu de servir le fichier original partout
- [ ] Tokens d’accès : TTL plus court + refresh, et nettoyage des tokens expirés en base
- [ ] Index Doctrine à revoir sous charge (commandes par client/date, médias par owner, tokens par hash)
- [ ] Debounce / cache HTTP lecture catalogue (ETag ou cache court) pour le GET public `/products`
- [ ] Uniformiser le wording checkout / paiement pour réduire les allers-retours inutiles

## Sécurité

### Auth
- [ ] Rate limiter login, register et checkout (pas de Symfony RateLimiter aujourd’hui)
- [ ] Ne plus stocker le Bearer token en clair dans `localStorage` (XSS) — cookie httpOnly + SameSite, ou au minimum un store mémoire
- [ ] Révoquer tous les tokens d’un user (logout global, changement de mot de passe)
- [ ] Politique mot de passe plus stricte que « 8 caractères » (et éventuellement argon2id options explicites)
- [ ] Centraliser l’auth (subscriber / middleware) : aujourd’hui chaque controller appelle `RequireAdmin` / Bearer à la main, oubli possible

### Paiement
- [ ] **Bloquer `POST /payments/complete` hors provider `local`** — le handler charge toujours `LocalPaymentGateway`, qui accepte tout. En prod un client peut marquer une commande Stripe comme payée sans payer
- [ ] Vérifier `successUrl` / `cancelUrl` (origine autorisée) avant de les envoyer à Stripe
- [ ] Idempotence webhook Stripe (rejeu `checkout.session.completed`)

### Médias / fichiers
- [ ] Valider le contenu réel du fichier, pas seulement le MIME déclaré
- [ ] Interdire ou sanitizer les SVG (XSS si servis en `image/svg+xml` depuis la même origine)
- [ ] Droits du dossier upload : `mkdir(..., 0777)` est trop permissif
- [ ] Servir `/uploads` hors exécution PHP, avec `Content-Type` / `X-Content-Type-Options: nosniff`

### HTTP / infra
- [ ] Headers de sécurité (`CSP`, `X-Frame-Options`, `Referrer-Policy`, HSTS en prod)
- [ ] CORS explicite (origines front autorisées) — pas de config dédiée aujourd’hui
- [ ] Ne jamais logger tokens, secrets Stripe, ni mots de passe
- [ ] Compte démo (`admin@shoppy.test`) : documenter qu’il ne doit pas exister en production
