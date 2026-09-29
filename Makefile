.PHONY: help dev prod down logs seed test

export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

COMPOSE_DEV := docker compose
COMPOSE_PROD := docker compose -f compose.prod.yaml

.DEFAULT_GOAL := help

help:
	@echo "Shoppy"
	@echo "  make dev    développement (HMR) — http://localhost:5173, pgAdmin http://localhost:5050"
	@echo "  make prod   production — https://localhost"
	@echo "  make down   arrêter dev et prod"
	@echo "  make logs   logs (dev)"
	@echo "  make seed   catalogue de démo (dev)"
	@echo "  make test   tests API sur Postgres (dev)"

dev:
	$(COMPOSE_DEV) up --build

prod:
	$(COMPOSE_PROD) up --build -d --wait

down:
	$(COMPOSE_DEV) down --remove-orphans
	POSTGRES_PASSWORD=unused APP_SECRET=unused $(COMPOSE_PROD) down --remove-orphans

logs:
	$(COMPOSE_DEV) logs -f --tail=100

seed:
	$(COMPOSE_DEV) exec api bin/console app:seed-demo

test:
	$(COMPOSE_DEV) up -d --wait database
	$(COMPOSE_DEV) run --rm --no-deps -T --entrypoint "" api composer test:parallel
