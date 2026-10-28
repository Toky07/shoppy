# Front Shoppy

SPA Vue 3 + TypeScript + Vite + Tailwind 4. Pas de Pinia : l’état vit dans des factories (`createAuthSession`, `createCartState`, …) injectées au boot.

Boutique : http://localhost:5173 (`make dev`). L’API est proxifiée sous `/api`.

## Lire dans l’ordre

| Fichier | Contenu |
|---------|---------|
| [architecture.md](architecture.md) | Couches, injection, boot, alias `@/` |
| [modules.md](modules.md) | Chaque module : rôle, pages, API consommée |
| [http.md](http.md) | Client HTTP, cookies, proxy, erreurs, argent |
| [auth.md](auth.md) | Session, gardes, invité, localStorage |
| [ui.md](ui.md) | i18n, thème, cookies, SEO, CSS, images |
| [testing.md](testing.md) | Vitest, `renderApp`, Playwright |

Côté API : [`api/docs/modules.md`](../../api/docs/modules.md), [authorization](../../api/docs/authorization.md), [email](../../api/docs/email.md), [stripe](../../api/docs/stripe.md).

## Commandes

```sh
cd front
npm run dev          # Vite, proxy /api → API
npm run test:unit    # Vitest (unit + composants)
npm run test:e2e     # Playwright
npm run build        # type-check + bundle
npm run lint
npm run format
```

Node `^22.18.0` ou `>=24.12.0`. En Docker, le service `front` monte le code et tourne `npm run dev` (HMR, polling).

## Variables

`front/.env.example` :

| Variable | Défaut | Rôle |
|----------|--------|------|
| `VITE_API_URL` | `/api` | Préfixe des appels `FetchHttpClient` (build-time) |

Docker / Vite (pas préfixées `VITE_`, donc **non** exposées au bundle) :

| Variable | Rôle |
|----------|------|
| `API_PROXY_TARGET` | Cible du proxy (`http://api:80` en compose, `http://127.0.0.1:8000` en local) |
| `VITE_IN_DOCKER` | Host, polling, HMR, `strictPort` |
| `VITE_USE_POLLING` | Watch fichiers (volumes Docker) |
| `VITE_HMR_HOST` / `VITE_HMR_CLIENT_PORT` / `VITE_PORT` | HMR depuis l’hôte |

En production (`compose.prod.yaml`), nginx sert `dist/` et reverse-proxifie `/api`, `/uploads`, `/media`, `/sitemap.xml`, `/robots.txt` vers l’API. `VITE_API_URL=/api` est figé au **build**.

## À retenir

- Cookie de session HttpOnly `shoppy_session` : `credentials: 'include'`. Le front ne stocke **pas** le jeton.
- Panier et favoris invités : `localStorage`, fusionnés au login.
- Guest checkout : email + lignes locales → l’API crée le compte, pose le cookie, le front appelle `authSession.set({ user })`.
- Paiement : `startCheckout` ; si `redirectUrl` → Stripe ; si `completedImmediately` → `POST /payments/complete` (provider local).
- Le PDF facture existe côté API (`GET /invoices/by-order/{id}/pdf`) : **pas encore d’écran front**.
- Textes UI : catalogues `fr` / `en` (`shared/i18n/locales`). Les pages légales sont encore en français dans le code.
- Argent : toujours des **centimes** (`Money`), jamais un float.
