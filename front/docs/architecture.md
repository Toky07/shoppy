# Architecture

## Emplacement

```
front/src/
  main.ts                 boot : provide + restoreAuthSession + router
  App.vue                 <RouterView /> uniquement
  app/dependencies.ts     composition racine (HTTP, repos, états)
  router/                 routes, gardes, scroll
  modules/{nom}/          un contexte métier
  shared/                 HTTP, i18n, UI, argent, tests
```

Alias Vite : `@` → `front/src`.

## Couches d’un module

Miroir de l’hexagone API, sans connaître PHP :

| Dossier | Rôle |
|---------|------|
| `domain/` | Types (`Product`, `Cart`, `Session`). Pas d’HTTP. |
| `application/` | Ports (`XxxRepository`), clés `InjectionKey`, use cases (`login`, `createCartState`, `useProductList`) |
| `data/` | `XxxHttpRepository` + mappers JSON → domaine. `InvalidResponseError` si le payload est inattendu. |
| `testing/` | Fakes + fixtures pour Vitest |
| `ui/pages/` | Écrans routés (`*Page.vue`) |
| `ui/components/` | Composants du module |
| `ui/layouts/` | Ex. `AdminLayout` |
| `ui/lib/` | Labels, messages d’erreur mappés depuis `ApiError.code` |

Un module **n’importe pas** le `data/` d’un autre. Il injecte un port. Exception assumée : `order` / `cart` réutilisent `PostalAddress` et `ShippingMethod` du domaine order.

Pas de Pinia (test `shared/__tests__/noUnusedPinia.spec.ts`).

## Injection

Les ports et états sont des `InjectionKey` (Symbol). `main.ts` fait `app.provide(...)`. Un composant :

```ts
const session = inject(authSessionKey)
if (!session) {
  throw new Error('Auth dependencies are not provided.')
}
```

Les tests rebranchent les mêmes clés via `renderApp()` (`shared/testing/renderApp.ts`).

## Boot (`main.ts` + `app/dependencies.ts`)

1. Purge l’ancien `localStorage['shoppy.session']` (jeton ne doit plus vivre en JS).
2. Construit `FetchHttpClient(VITE_API_URL, fetch, authSession)`.
3. Instancie tous les HTTP repositories.
4. `restoreAuthSession` : si `shoppy.has-session === '1'`, `GET /auth/me` ; sinon session vide.
5. `await authReady` **avant** d’installer le garde router (évite un flash login).
6. `router.beforeEach(createNavigationGuard(authSession))`.
7. `app.mount('#app')`.

Polices self-hostées (`@fontsource-variable/inter`, `bricolage-grotesque`). CSS : `assets/main.css`.

## Router

`createWebHistory`. Pages en `() => import(...)` (code-splitting). Layouts **synchrones** : `AppLayout` (boutique), `AdminLayout` (back-office).

`meta.requiresAuth` / `meta.requiresAdmin` : voir [auth.md](auth.md).

`safeRedirectPath` n’accepte qu’un chemin commençant par `/` (pas `//evil`).

## Shared

| Dossier | Rôle |
|---------|------|
| `http/` | `HttpClient`, `FetchHttpClient`, `ApiError`, query, join URL |
| `money/` | `Money` `{ cents, currency }`, `formatMoney`, `mapMoney` |
| `i18n/` | Locale `fr` \| `en`, catalogues JSON, `useI18n` |
| `theme/` | Clair / sombre / système |
| `consent/` | Bannière cookies |
| `seo/` | Title, description, Open Graph |
| `media/` | Dérivés d’images `/uploads/…` |
| `ui/` | Layout, header, footer, boutons, icônes, pagination… |
| `testing/` | `renderApp`, locale de test |
| `routing/` | Query `page` / `q`, redirect sûr |
| `datetime/` | Dates localisées |
| `async/` | `usePendingAction` (pending + mapping d’erreur) |

## Flux d’achat côté UI

```
Catalogue / favoris
  → panier (localStorage si anonyme, API si session)
  → /cart checkout (adresses + méthode + email invité)
  → /orders/:id
      startCheckout
        ├─ redirectUrl        → Stripe Checkout
        ├─ ?payment=success   → confirm
        └─ completedImmediately → complete (local)
```

Annulation client si commande `pending`. L’admin peut `markPaid` depuis `/admin/orders/:id`.
