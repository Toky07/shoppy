.PHONY: help dev prod down logs seed

export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

COMPOSE_DEV := docker compose
COMPOSE_PROD := docker compose -f compose.prod.yaml

.DEFAULT_GOAL := help

help:
	@echo "Shoppy"
	@echo "  make dev    développement (HMR) — http://localhost:5173"
	@echo "  make prod   production — http://localhost"
	@echo "  make down   arrêter dev et prod"
	@echo "  make logs   logs (dev)"
	@echo "  make seed   catalogue de démo (dev)"

dev:
	$(COMPOSE_DEV) up --build

prod:
	$(COMPOSE_PROD) up --build -d --wait

down:
	$(COMPOSE_DEV) down --remove-orphans
	$(COMPOSE_PROD) down --remove-orphans

logs:
	$(COMPOSE_DEV) logs -f --tail=100

seed:
	$(COMPOSE_DEV) exec api bin/console app:seed-demo
