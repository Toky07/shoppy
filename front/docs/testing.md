# Tests

## Unit + composants (Vitest)

```sh
cd front && npm run test:unit
```

Deux projets (`vitest.config.ts`) :

| Projet | Environnement | Quoi |
|--------|---------------|------|
| `unit` | `node` | mappers, use cases, repos HTTP (avec `FakeHttpClient`) |
| `component` | `happy-dom` | `*Page.vue`, composants UI, `createCartState` / favoris / carnet |

`isolate: false` pour la vitesse. Setup composant : `vitest.setup.ts` (IntersectionObserver, `confirm()` → false, `stepMismatch` happy-dom).

Alias `@` identique à Vite.

## `renderApp`

Helper unique : monte `App.vue` + **les vraies routes** + le garde auth, avec des **fakes** par défaut.

```ts
const { router, authSession } = await renderApp({
  path: '/cart',
  session: { user: aCustomer },
  cartRepository: new FakeCartRepository([aLine]),
})
```

Options : chaque repository, `session`, `locale`, `cookieConsent`, `path`.

Ne pas recréer un mini-router dans un spec de page : passer par `renderApp` pour rester aligné sur les `provide`.

Fakes : `modules/*/testing/FakeXxxRepository.ts`. HTTP brut : `shared/testing/FakeHttpClient.ts`.

## Conventions

- Spec à côté du code : `data/__tests__/ProductHttpRepository.spec.ts`, `ui/pages/__tests__/CartPage.spec.ts`.
- Un repository HTTP se teste en asserting le path / body envoyé au fake client, et le mapping du JSON.
- Les pages cliquent vraiment (Testing Library + `user-event`) : ajouter au panier, payer, erreurs `ApiError`.
- Messages : passer par les catalogues (`t(...)`) plutôt que des chaînes en dur, ou accepter fr par défaut (`renderApp` locale `fr`).

## E2E (Playwright)

`front/e2e/`. Config : Chromium, Firefox, WebKit. Dev server `npm run dev` (port 5173) ; CI : `npm run preview` (4173).

Aujourd’hui un smoke : la home affiche le titre catalogue. Les parcours métier sont couverts en Vitest composant + tests API Pest.

```sh
cd front && npm run test:e2e
```

## Lint / types

`npm run lint` (oxlint puis eslint). `vue-tsc` dans `npm run build`. Prettier sur `src/`.
