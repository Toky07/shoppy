# Shoppy

## Développement

```sh
make dev
```

- Boutique : http://localhost:5173
- API : http://localhost:8000
- Rechargement à chaud Vite activé

## Production

```sh
make prod
```

Boutique + API derrière nginx : http://localhost

## Utilitaires

```sh
make down    # arrêter
make logs    # suivre les logs
make seed    # catalogue de démo
```

Comptes démo : `admin@shoppy.test` / `visitor@shoppy.test`, mot de passe `password123`.

Paiement : `PAYMENT_PROVIDER=local` par défaut, ou `stripe` avec `STRIPE_SECRET_KEY` et `STRIPE_WEBHOOK_SECRET`. En production, définissez aussi `APP_SECRET`.

Les Dockerfiles sont dans `api/docker/` et `front/docker/`.
