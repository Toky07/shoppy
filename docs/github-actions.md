# GitHub Actions

Fichier : [`.github/workflows/ci.yml`](../.github/workflows/ci.yml). Un seul workflow, nommé **CI**.

Il tourne à **chaque pull request**. Pas sur `push` vers `main`, pas de déploiement automatique.

```
PR ouverte / mise à jour
        │
        ├── API tests          → PHP + Postgres + Redis
        ├── Front tests        → lint + Vitest + build Vite
        └── Production images  → Dockerfiles prod (sans publier)
```

Permissions : `contents: read` seulement. Le workflow ne pousse rien, ne crée pas de release, n’écrit pas sur le dépôt.

---

## API tests

Même idée que `make test` : Pest en parallèle, sur PostgreSQL 16 et Redis 7 (services du job).

1. PHP 8.3 + extensions (`pdo_pgsql`, `redis`, `gd`, …).
2. `composer install`.
3. Migrations : `migrate` → `doctrine:schema:validate` (le schéma Doctrine = les migrations) → `migrate first` (les migrations se défont aussi).
4. `composer test:parallel`.

Échec typique : test rouge, migration qui ne matche plus les entités, `composer.lock` incohérent.

---

## Front tests

Dans `front/` :

1. Node 22, `npm ci`.
2. `npm run lint:ci` (oxlint puis eslint, **sans** `--fix` : le runner ne réécrit pas les fichiers).
3. `npm run test:unit -- --run` (Vitest, un passage, pas watch).
4. `npm run build` (type-check `vue-tsc` + bundle Vite).

Ça valide le **code** et que le bundle se produit **sur le runner** (pas dans l’image Docker). Playwright / e2e ne tourne pas ici.

---

## Production images

### Rôle

Vérifier que les **images Docker de production se construisent**, celles que `make prod` / `compose.prod.yaml` utilisent vraiment.

Les jobs « API tests » et « Front tests » ne passent jamais par `api/docker/Dockerfile` ni `front/docker/Dockerfile`. On peut avoir des tests verts et une image **inbuildable** (fichier `COPY` manquant, `composer install --no-dev` cassé, nginx qui ne trouve pas sa conf, stage `production` qui a divergé du stage `development`).

Sans ce job, on ne le découvre qu’en lançant `make prod` sur une machine, souvent trop tard.

**Production images = filet de déploiement.** Pas un déploiement.

### Ce qu’il fait concrètement

1. Installe [Buildx](https://github.com/docker/setup-buildx-action) (le builder Docker moderne, avec cache).
2. Construit l’image **API**, contexte `api/`, fichier `api/docker/Dockerfile`, **stage `production`**.
3. Construit l’image **front**, contexte `front/`, fichier `front/docker/Dockerfile`, **stage `production`**.

`push: false` : l’image reste sur le runner et **disparaît** à la fin du job. Rien n’est envoyé vers Docker Hub, ni aucun registre. Aucun secret de prod n’est injecté.

Le cache GitHub Actions (`type=gha`, scopes `api` / `front`) mémorise les couches (Composer, `npm ci`, apt…). Les PR suivantes reconstruisent surtout ce qui a changé.

### Image API (`target: production`)

Reproduit `compose.prod.yaml` → service `api` :

| Étape Dockerfile | Effet |
|------------------|--------|
| `base` | FrankenPHP / PHP 8.3, extensions (`pdo_pgsql`, `redis`, `gd`, …) |
| `build` | `composer install --no-dev`, autoload, warmup du cache Symfony **prod** |
| `production` | `php.ini-production`, user non-root `app` (uid 10001), écoute **8080**, healthcheck `/health` |

Au build, des placeholders (`APP_SECRET=build-time-only`, `PAYMENT_PROVIDER=local`, …) existent **uniquement** pour que `cache:warmup` tourne. Ils ne sont pas dans l’image runtime : `compose.prod.yaml` fournit les vrais `APP_SECRET`, `DATABASE_URL`, Stripe, mail, etc. au **démarrage** du conteneur.

`.env.dev` est retiré de l’image (`rm -rf … .env.dev`). Les secrets ne sont pas cuits dedans.

Ce que ce job attrape : Dockerfile cassé, `composer.lock` qui n’installe plus en `--no-dev`, script Symfony qui échoue au warmup, fichier manquant dans le contexte Docker (`COPY . .` / `.dockerignore`).

Ce qu’il n’attrape pas : une requête HTTP réelle, une migration contre une base, Stripe, SMTP. Le conteneur n’est **pas démarré**.

### Image front (`target: production`)

Reproduit `compose.prod.yaml` → service `front` :

| Étape Dockerfile | Effet |
|------------------|--------|
| `deps` | `npm ci` |
| `build` | `npm run build-only` avec `VITE_API_URL=/api` (figé dans le JS) |
| `certs` | certificat TLS auto-signé (HTTPS local / `make prod`) |
| `production` | nginx **unprivileged** 1.27, fichiers `dist/`, reverse-proxy `/api`, `/uploads`, `/media`, `/sitemap.xml`, `/robots.txt` vers l’API |

nginx écoute 8080 (HTTP → redirect) et 8443 (HTTPS). C’est la **porte d’entrée** de la prod : le navigateur ne parle pas à FrankenPHP directement.

Ce que ce job attrape : `Dockerfile` / `nginx.conf` / snippets introuvables, `npm ci` ou `build-only` qui échoue **dans** l’image (différent du job Front tests : ici pas de `vue-tsc` séparé, c’est `build-only`), contexte Docker incomplet.

Même chose : le conteneur n’est pas lancé, aucun `wget` healthcheck n’est exécuté par la CI. Seul le **build** compte.

### Tableau

| | Production images | `make prod` |
|--|-------------------|-------------|
| Construit les stages `production` | oui | oui |
| Pousse l’image vers un registre | non | non (build local) |
| Démarre Postgres, Redis, API, nginx | non | oui |
| Injecte `APP_SECRET`, Stripe, SMTP | non | oui (env) |
| Joue les migrations | non | oui (entrypoint API) |
| Sert https://localhost | non | oui |

Les deux jobs tests + celui-ci ensemble répondent : « le code est correct **et** on peut encore fabriquer les artefacts qu’on mettrait en production ».

### Lire un échec

- **Build API image** rouge → ouvrir le log du step, souvent Composer, `cache:warmup`, ou un `COPY` vers un fichier absent.
- **Build front image** rouge → `npm ci` / `vite build` dans Docker, ou un fichier nginx manquant.
- Vert ici, rouge « Front tests » → le type-check (`vue-tsc`) ou Vitest a cassé ; l’image prod ne type-check pas (`build-only`).
- Vert ici, rouge « API tests » → Pest ou les migrations ; l’image s’est quand même construite.

Pour reproduire en local, sans CI :

```sh
docker build -f api/docker/Dockerfile --target production api
docker build -f front/docker/Dockerfile --target production --build-arg VITE_API_URL=/api front
```

Ou directement `POSTGRES_PASSWORD=… APP_SECRET=… make prod`, qui construit **et** démarre la stack.
