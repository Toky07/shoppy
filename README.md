# Shoppy

## Développement

```sh
make dev
```

- Boutique : http://localhost:5173
- API : http://localhost:8000
- pgAdmin : http://localhost:5050 (`admin@example.com` / `shoppy`). Le serveur « Shoppy » est déjà déclaré ; mot de passe de la base : `shoppy`.
- PostgreSQL 16 : `127.0.0.1:5432`, base `shoppy`, utilisateur `shoppy`, mot de passe `shoppy`
- Rechargement à chaud Vite activé

Les ports de l’API, de Postgres et de pgAdmin ne sont exposés que sur `127.0.0.1`. Les identifiants se changent avec `POSTGRES_USER`, `POSTGRES_PASSWORD`, `POSTGRES_DB`, `PGADMIN_EMAIL` et `PGADMIN_PASSWORD` (par exemple dans un fichier `.env` à la racine, ignoré par git).

## Base de données

Le schéma est géré par les migrations Doctrine (`api/migrations/`), jouées automatiquement au démarrage de l’API. Après une modification des entités :

```sh
docker compose exec api bin/console doctrine:migrations:diff
```

## Tests

```sh
make test
```

Les tests de l’API tournent sur PostgreSQL, en parallèle : chaque worker utilise sa propre base (`shoppy_test1`, `shoppy_test2`…), créée automatiquement. Pour les lancer hors Docker, PHP doit avoir l’extension `pdo_pgsql` (`sudo apt install php8.3-pgsql`) et Postgres doit tourner (`docker compose up -d database`), puis `cd api && composer test:parallel`.

Front : `cd front && npm run test:unit`.

## Production

```sh
POSTGRES_PASSWORD=... APP_SECRET=... make prod
```

Boutique + API derrière nginx : https://localhost (le port 80 redirige vers HTTPS, certificat auto-signé). `POSTGRES_PASSWORD` et `APP_SECRET` sont obligatoires ; `APP_SECRET` ne doit pas valoir `change-me-in-production`. `api/.env.dev` est un secret de développement versionné, à ne pas réutiliser en production. `PAYMENT_PROVIDER=local` est refusé en production.

Sécurité des conteneurs : API et nginx tournent sans root, toutes les capabilities sont retirées (`cap_drop: ALL`, `no-new-privileges`), nginx a un système de fichiers en lecture seule, Postgres est sur un réseau interne sans port publié, et aucun secret n’est inscrit dans les images.

## Utilitaires

```sh
make down    # arrêter
make logs    # suivre les logs
make seed    # catalogue de démo
```

Pour repartir d’une base vide avant le seed : `docker compose exec api bin/console app:seed-demo --reset`.

Comptes démo : `admin@shoppy.test` / `visitor@shoppy.test`, mot de passe `password123`. Le seed refuse de tourner en production.

Paiement : `PAYMENT_PROVIDER=local` par défaut en développement, ou `stripe` avec `STRIPE_SECRET_KEY` et `STRIPE_WEBHOOK_SECRET`. En production le provider local est refusé.

Emails : `MAILER_DSN=null://null` par défaut (aucun envoi). Expéditeur : `MAILER_FROM`. Les autres modules demandent un envoi en publiant `EmailRequested` sur le bus d’événements. L’événement accepte des pièces jointes. Une commande confirmée joint `recu.txt`.

Les Dockerfiles sont dans `api/docker/` et `front/docker/`.

## Structure des modules front

Chaque domaine (`auth`, `catalog`, `cart`, `order`, `admin`, `payment`) suit le même découpage :

- `domain/` — modèles et règles métier
- `data/` — API HTTP, mappers
- `application/` — ports, use cases, composables (`use…`), clés d’injection
- `testing/` — fakes et fixtures
- `ui/pages/` — écrans routés (`*Page.vue`)
- `ui/components/` — composants réutilisables du module
- `ui/layouts/` — layouts (ex. admin)
- `ui/lib/` — helpers UI (labels, messages d’erreur, brouillons)
