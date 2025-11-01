# ===========================
# SISTEMA CLÍNICAS - MAKEFILE
# ===========================

.PHONY: bootstrap up down logs restart migrate seed npm-dev logs-frontend

# -----------------------------------------
# 🚀 Inicializa todo o ambiente (primeira vez ou reset total)
# -----------------------------------------
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

# -----------------------------------------
# 🔼 Sobe containers já existentes
# -----------------------------------------
up:
	docker compose -f ops/docker/dev/docker-compose.dev.yml up -d
	@echo "\n🟢 Containers em execução!"
	@echo "Frontend: http://localhost:5173"
	@echo "Backend (Laravel): http://localhost:8080"
	@echo "Mailpit: http://localhost:8025"

# -----------------------------------------
# 🔻 Derruba todos os containers
# -----------------------------------------
down:
	docker compose -f ops/docker/dev/docker-compose.dev.yml down
	@echo "\n🛑 Containers encerrados!"

# -----------------------------------------
# 📜 Logs em tempo real (todos os serviços)
# -----------------------------------------
logs:
	docker compose -f ops/docker/dev/docker-compose.dev.yml logs -f

# -----------------------------------------
# 🧩 Logs apenas do frontend (Vite)
# -----------------------------------------
logs-frontend:
	docker compose -f ops/docker/dev/docker-compose.dev.yml logs -f frontend

# -----------------------------------------
# 🔁 Reinicia containers (sem rebuild)
# -----------------------------------------
restart:
	make down && make up

# -----------------------------------------
# 🔄 Reaplicar migrations (sem resetar tudo)
# -----------------------------------------
migrate:
	docker compose -f ops/docker/dev/docker-compose.dev.yml exec php php artisan migrate
	@echo "\n✅ Migrations executadas!"

# -----------------------------------------
# 🌱 Rodar seeds manualmente
# -----------------------------------------
seed:
	docker compose -f ops/docker/dev/docker-compose.dev.yml exec php php artisan db:seed
	@echo "\n✅ Seeds executados!"

# -----------------------------------------
# 💻 Rodar servidor local do frontend manualmente (opcional)
# -----------------------------------------
npm-dev:
	cd frontend && npm run dev -- --host 0.0.0.0 --port 5173 --strictPort
