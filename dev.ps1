<#
.SYNOPSIS
    Script auxiliar para Windows - substitui os comandos do Makefile.
.DESCRIPTION
    Uso: .\dev.ps1 <comando>
    Comandos: bootstrap, up, down, logs, logs-frontend, restart, migrate, seed
#>

param(
    [Parameter(Position = 0)]
    [ValidateSet("bootstrap", "up", "down", "logs", "logs-frontend", "restart", "migrate", "seed")]
    [string]$Command
)

$ComposeFile = "ops/docker/dev/docker-compose.dev.yml"
$DC = "docker compose -f $ComposeFile"

function Show-Help {
    Write-Host ""
    Write-Host "Comandos disponiveis:" -ForegroundColor Cyan
    Write-Host "  bootstrap      - Cria e inicializa todo o ambiente do zero"
    Write-Host "  up             - Sobe containers existentes"
    Write-Host "  down           - Derruba todos os containers"
    Write-Host "  logs           - Mostra logs de todos os servicos"
    Write-Host "  logs-frontend  - Mostra logs do frontend"
    Write-Host "  restart        - Reinicia containers"
    Write-Host "  migrate        - Executa migrations"
    Write-Host "  seed           - Executa seeders"
    Write-Host ""
    Write-Host "Uso: .\dev.ps1 <comando>" -ForegroundColor Yellow
}

switch ($Command) {
    "bootstrap" {
        if (-not (Test-Path "backend\.env")) {
            Copy-Item "backend\.env.example" "backend\.env"
            Write-Host "Arquivo .env criado." -ForegroundColor Green
        }
        Invoke-Expression "$DC up -d --build"
        Write-Host ""
        Write-Host "Ambiente inicializado com sucesso!" -ForegroundColor Green
        Write-Host "Frontend:  http://localhost:5174"
        Write-Host "Backend:   http://localhost:8080"
        Write-Host "Mailpit:   http://localhost:8025"
    }
    "up" {
        Invoke-Expression "$DC up -d"
        Write-Host ""
        Write-Host "Containers em execucao!" -ForegroundColor Green
        Write-Host "Frontend:  http://localhost:5174"
        Write-Host "Backend:   http://localhost:8080"
        Write-Host "Mailpit:   http://localhost:8025"
    }
    "down" {
        Invoke-Expression "$DC down"
        Write-Host "Containers encerrados!" -ForegroundColor Red
    }
    "logs" {
        Invoke-Expression "$DC logs -f"
    }
    "logs-frontend" {
        Invoke-Expression "$DC logs -f frontend"
    }
    "restart" {
        Invoke-Expression "$DC down"
        Invoke-Expression "$DC up -d"
        Write-Host "Containers reiniciados!" -ForegroundColor Green
    }
    "migrate" {
        Invoke-Expression "$DC exec php php artisan migrate"
        Write-Host "Migrations executadas!" -ForegroundColor Green
    }
    "seed" {
        Invoke-Expression "$DC exec php php artisan db:seed"
        Write-Host "Seeds executados!" -ForegroundColor Green
    }
    default {
        Show-Help
    }
}
