# HTTP

Tout le réseau passe par `HttpClient` (`get` / `post` / `postForm` / `put` / `patch` / `delete`). Implémentation : `FetchHttpClient`.

## Base URL

`import.meta.env.VITE_API_URL` (défaut `/api`). `joinUrl` concatène sans double slash.

Le client **ne préfixe pas** les chemins métier : un repository appelle `http.get('/products')`, ce qui devient `GET /api/products` en dev et en prod.

## Cookies, pas Bearer

Chaque requête : `credentials: 'include'`. L’API pose `shoppy_session` (HttpOnly). Le front n’envoie pas `Authorization`.

`AccessTokenProvider` (`authSession`) ne sert qu’à **clear** la présence locale si l’API répond `unauthenticated`.

## Refresh automatique

Sur `401` / code `unauthenticated`, **sauf** `/auth/login` et `/auth/refresh` :

1. Un seul `POST /auth/refresh` en vol (`refreshInFlight`).
2. Si OK → rejoue la requête une fois.
3. Sinon → `tokenProvider.clear()` (oublie `shoppy.has-session`).

`204 No Content` → `undefined`. Sinon `response.json()`.

`postForm` : `FormData` sans `Content-Type` JSON (boundary multipart) — upload média admin.

## Erreurs

Corps API attendu :

```json
{
  "error": {
    "code": "insufficient_product_stock",
    "message": "…",
    "violations": [{ "field": "email", "message": "…" }]
  }
}
```

`parseApiError` → `ApiError(status, code, message, violations)`. Payload inattendu : `code = http_error`.

Les pages ne montrent pas le `message` brut anglais : `ui/lib/*ErrorMessage.ts` mappe `code` → clé i18n (`t('cart.errors.insufficientStock')`).

Un mapper `data/` qui ne reconnaît pas le JSON lève `InvalidResponseError` (bug contrat, pas métier).

## Proxy Vite (dev)

`vite.config.ts` :

| Préfixe | Cible | Rewrite |
|---------|-------|---------|
| `/api` | `API_PROXY_TARGET` | enlève `/api` |
| `/media` | idem | inchangé |
| `/uploads` | idem | inchangé |
| `/sitemap.xml` | idem | inchangé |
| `/robots.txt` | idem | inchangé |

Les images catalogue sont des URLs `/uploads/…` servies par l’API, d’où le proxy (et nginx en prod).

## Production

`front/docker/nginx.conf` : SPA `try_files` + mêmes reverse-proxies. HTTPS 8443 (certificat auto-signé en local prod). En-têtes de sécu dans `security-headers.conf`.

## Argent

Type unique :

```ts
type Money = { cents: number; currency: string }
```

`mapMoney` lit `{ cents, currency }` (ou équivalent API). `formatMoney` + locale i18n. Les filtres catalogue convertissent des euros entiers saisis → centimes (`catalogCentsFromEuros`). Pas de `number` flottant pour un prix.
