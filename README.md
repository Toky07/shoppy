# Shoppy

```sh
docker compose up --build
```

- Boutique : http://localhost:5173
- API : http://localhost:8000

Les Dockerfiles restent dans `api/docker/` et `front/docker/`. Le rechargement à chaud Vite est actif en développement.

Catalogue de démo : `docker compose exec api bin/console app:seed-demo`  
Comptes : `admin@shoppy.test` / `visitor@shoppy.test`, mot de passe `password123`.

Paiement : `PAYMENT_PROVIDER=local` par défaut, ou `stripe` avec `STRIPE_SECRET_KEY` et `STRIPE_WEBHOOK_SECRET`.
