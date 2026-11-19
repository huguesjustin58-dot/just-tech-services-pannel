.PHONY: up down build migrate seed sync-vpn test backup

COMPOSE = docker compose --env-file backend/.env

up:
	$(COMPOSE) up -d

build:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

migrate:
	$(COMPOSE) exec app php artisan migrate --force

seed:
	$(COMPOSE) exec app php artisan db:seed --force

sync-vpn:
	$(COMPOSE) exec app php artisan jts:vpn-sync-accounts

test:
	$(COMPOSE) exec app php artisan test

backup:
	mkdir -p backups
	$(COMPOSE) exec -T postgres pg_dump -U $${DB_USERNAME:-jts_user} -Fc $${DB_DATABASE:-just_tech_panel} | gzip > backups/jts_$$(date +%Y%m%d_%H%M).dump.gz
