# 🏥 Sistema Clínicas (SGC)

**Sistema Gerencial de Clínicas (SGC)** — uma plataforma completa para gestão de clínicas, pacientes, profissionais e atendimentos.  
Desenvolvido com **Laravel (Backend)** + **Vue 3 + Vite (Frontend)**, utilizando **Docker** para garantir ambientes reproduzíveis em qualquer máquina.

---

## 🚀 Tecnologias Principais

| Camada | Tecnologia | Descrição |
|--------|-------------|-----------|
| **Frontend** | Vue 3 + Vite + TailwindCSS | Interface moderna e reativa |
| **Backend** | Laravel 11 (PHP 8.3) | API RESTful e serviços internos |
| **Banco de Dados** | MySQL 8 | Armazenamento de dados relacional |
| **Cache / Sessões** | Redis 7 | Cache, filas e sessões |
| **Proxy reverso** | Nginx 1.27 | Orquestra requisições entre containers |
| **Email Dev** | Mailpit | Visualizador de e-mails de teste |
| **Infraestrutura** | Docker + Docker Compose | Ambientes isolados e automatizados |

---

## ⚙️ Pré-requisitos

Antes de iniciar, garanta que você possui:

| Dependência | Versão mínima | Observação |
|--------------|----------------|------------|
| **Docker Desktop** | 4.27+ | Necessário para rodar containers |
| **Docker Compose** | 2.20+ | Incluído no Docker Desktop |
| **Make** | 4.3+ | Pré-instalado em macOS/Linux. No Windows use `make` do WSL2 |
| **Git** | 2.40+ | Para clonar o repositório |

---

## 🧩 Estrutura do Projeto

```
sistema_clinicas/
├── backend/                # Código do Laravel
├── frontend/               # Código do Vue 3 + Vite
├── ops/                    # Infraestrutura Docker
│   ├── dev/
│   └── prod/
├── Makefile
└── README.md
```

---

## 🧰 Comandos Disponíveis (`Makefile`)

| Comando | Descrição |
|----------|------------|
| `make bootstrap` | Cria e inicializa todo o ambiente de desenvolvimento do zero |
| `make up` | Sobe containers existentes sem rebuild |
| `make down` | Derruba todos os containers |
| `make logs` | Mostra logs de todos os serviços |
| `make logs-frontend` | Mostra logs apenas do frontend |
| `make migrate` | Executa migrations |
| `make seed` | Executa os seeders |
| `make restart` | Reinicia containers sem rebuild |

---

## 🧪 Ambiente de Desenvolvimento

### 🔹 Passos para iniciar do zero

```bash
git clone https://github.com/williamsestito/sistema_clinicas.git
cd sistema_clinicas
make bootstrap
```

---

### 🔹 Acessos

| Serviço | URL | Porta |
|----------|-----|-------|
| **Frontend (Vite)** | [http://localhost:5173](http://localhost:5173) | 5173 |
| **Backend (Laravel via Nginx)** | [http://localhost:8080](http://localhost:8080) | 8080 |
| **Mailpit (E-mails de teste)** | [http://localhost:8025](http://localhost:8025) | 8025 |
| **MySQL** | `localhost:3306` | Usuário: `laravel` / Senha: `laravel` |

---

### 🔹 Monitorar logs

```bash
make logs
make logs-frontend
```

---

### 🔹 Resetar tudo

```bash
make down
docker system prune -af --volumes
make bootstrap
```

---

## 🏗️ Ambiente de Produção

O ambiente de produção possui build otimizado e sem hot reload.

### Deploy simplificado

```bash
cd frontend
npm install && npm run build
cd ..
docker compose -f ops/docker/prod/docker-compose.prod.yml up -d --build
```

---

## 🧑‍💻 Contribuição

```bash
git checkout -b feature/nome-da-feature
git commit -m "feat: descrição da feature"
git push origin feature/nome-da-feature
```

---

## 🔒 Licença

Este projeto é licenciado sob a **MIT License**.

---

📘 **Autor:**  
**Sestito** — Tech Lead e Professor de Desenvolvimento Web & Mobile  
🚀 _"Automatize tudo. Configure uma vez, rode em qualquer lugar."_
