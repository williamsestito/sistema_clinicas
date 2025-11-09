

.SILENT:

ifeq ($(OS),Windows_NT)
SHELL := C:/Program Files/Git/bin/bash.exe
else
SHELL := /usr/bin/env bash
endif

.SHELLFLAGS := -o pipefail -c

.PHONY: bootstrap up down logs restart migrate seed npm-dev logs-frontend

bootstrap:
	cd backend && cp -n .env.example .env || true
	docker compose -f ops/docker/dev/docker-compose.dev.yml up -d --build
	sleep 8
	docker compose -f ops/docker/dev/docker-compose.dev.yml exec php php artisan key:generate
	docker compose -f ops/docker/dev/docker-compose.dev.yml exec php php artisan migrate:fresh --seed
	@echo "\n✅ Ambiente inicializado com sucesso!"
	@echo "Frontend: http://localhost:5173"
	@echo "Backend (Laravel): http://localhost:8080"
	@echo "Mailpit: http://localhost:8025"

up:
	docker compose -f ops/docker/dev/docker-compose.dev.yml up -d
	@echo "\n🟢 Containers em execução!"
	@echo "Frontend: http://localhost:5173"
	@echo "Backend (Laravel): http://localhost:8080"
	@echo "Mailpit: http://localhost:8025"

down:
	docker compose -f ops/docker/dev/docker-compose.dev.yml down
	@echo "\n🛑 Containers encerrados!"

logs:
	docker compose -f ops/docker/dev/docker-compose.dev.yml logs -f

logs-frontend:
	docker compose -f ops/docker/dev/docker-compose.dev.yml logs -f frontend

restart:
	$(MAKE) down && $(MAKE) up

migrate:
	docker compose -f ops/docker/dev/docker-compose.dev.yml exec php php artisan migrate
	@echo "\n✅ Migrations executadas!"

seed:
	docker compose -f ops/docker/dev/docker-compose.dev.yml exec php php artisan db:seed
	@echo "\n✅ Seeds executados!"

npm-dev:
	cd frontend && npm run dev -- --host 0.0.0.0 --port 5173 --strictPort
