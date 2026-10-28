# Auth, session, invité

Détail API : [`api/docs/authorization.md`](../../api/docs/authorization.md).

## Qui est connecté

`createAuthSession` tient `session: { user } | null`, `isAuthenticated`, `isAdmin` (`user.role === 'admin'`).

`User` : `id`, `email`, `role` (`customer` | `admin`), `createdAt`, `emailVerified`.

Le cookie HttpOnly porte le jeton. Le JS ne le lit pas.

`localStorage['shoppy.has-session'] = '1'` est un **drapeau** : au boot, s’il est présent, `GET /auth/me` restaure l’utilisateur. S’il est absent, on n’appelle pas `/auth/me` (évite un 401 inutile).

Ancien `shoppy.session` (jeton en clair) : supprimé au boot.

## Login / logout

`login(repository, session, credentials)` → `POST /auth/login` → `session.set`. L’API pose le cookie ; le front mémorise le drapeau.

`logout` → `POST /auth/logout` → `session.clear`.

`safeRedirectPath(route.query.redirect)` après login (chemin interne seulement). Login boutique : `/login`. Login admin : `/admin/login` (si déjà `customer`, message « pas admin »).

## Gardes router

`createNavigationGuard(authSession)` :

| Meta | Non connecté | Connecté non-admin |
|------|----------------|--------------------|
| `requiresAuth` | `/login?redirect=` | OK |
| `requiresAdmin` | `/admin/login?redirect=` | `/admin/login` (refus rôle) |

`requiresAdmin` implique auth. Le layout `/admin` porte `requiresAdmin` : toutes les pages enfants sont protégées.

Les pages compte / commandes ont **aussi** un `AuthRequiredPanel` si on arrive sans session (filet UI).

## Compte

Routes publiques : login, register, forgot / reset password, verify / confirm email.

`/account` (`requiresAuth`) : identité, activité, préférences (thème, langue), carnet d’adresses (`account`), sécurité (mot de passe, email, logout-all, suppression).

Tokens des mails : query `?token=` sur `/reset-password`, `/verify-email`, `/confirm-email`.

## Invité

### Panier

Sans session, `createCartState` lit/écrit `localStorage['shoppy.guestCart']` (lignes : produit, variante, qty, nom, prix, stock, image). Au passage `isAuthenticated → true`, `POST /cart/merge` puis vidage local.

Checkout invité : email obligatoire + `items` du guest cart → `POST /cart/checkout`. L’API crée le compte et le cookie. Si la réponse contient `user`, `CartPage` fait `authSession.set({ user })` puis redirige vers `/orders/:id`.

### Favoris

Même schéma : `shoppy.favorites` (ids), `POST /favorites/merge` au login.

### Adresses

Pas de carnet local. L’API mémorise l’adresse de livraison à `OrderPlaced`. Le panel compte liste / défaut / suppression.

## localStorage

| Clé | Contenu |
|-----|---------|
| `shoppy.has-session` | `'1'` si une session cookie est attendue |
| `shoppy.guestCart` | Panier anonyme |
| `shoppy.favorites` | Favoris anonymes |
| `shoppy.locale` | `fr` \| `en` |
| `shoppy.theme` | `light` \| `dark` \| `system` |
| `shoppy.cookie-consent` | `necessary` \| `all` |

Tout accès storage est try/catch (navigation privée).
