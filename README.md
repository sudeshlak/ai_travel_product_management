# AI Travel Product Management

Local setup guide for the full stack (Laravel API + React frontend) using Docker Compose.

## Prerequisites

Install these on your computer before starting:

- **Docker Desktop** (macOS / Windows) or **Docker Engine + Compose** (Linux)
- **MySQL 8.x** running on the host (Compose does not start a database container)
- **Git**

PHP, Composer, and Node.js are **not** required on the host when using Docker.
The images already include PHP 8.4 and Node 22.

## Database

Create one MySQL database. Name and credentials must match `backend/.env`.
- Database: `ai_travel_product_management`
- Username: `root`
- Password: *(empty, or set to match your local MySQL)*

## Environment variables

### Backend

Update in `backend/.env`:

| Variable | Purpose | Typical local value |
| --- | --- | --- |
| `APP_KEY` | Laravel encryption key | Leave empty; Docker entrypoint generates it if missing |
| `APP_URL` | Backend URL | `http://localhost:8000` |
| `FRONTEND_URL` | Frontend origin (CORS) | `http://localhost:5173` |
| `DB_HOST` | MySQL host | `127.0.0.1` in the file; Compose overrides to `host.docker.internal` |
| `DB_PORT` | MySQL port | `3306` |
| `DB_DATABASE` | Database name | `ai_travel_product_management` |
| `DB_USERNAME` | MySQL user | Match your local MySQL (e.g. `root`) |
| `DB_PASSWORD` | MySQL password | Match your local MySQL |
| `OPENAI_API_KEY` | AI features | Your OpenAI API key |
| `OPENAI_MODEL` | Model name | `gpt-4o-mini` (default) |

### Frontend

Update `frontend/.env`:

| Variable | Purpose | Typical local value |
| --- | --- | --- |
| `VITE_API_BASE_URL` | Backend API base URL | `http://localhost:8000/api` |

## Run the project

From the repository root:
```bash
docker compose up --build
```

After the containers are up, run migrations and seeders once:
```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

App URLs:
- Frontend: http://localhost:5173
- Backend: http://localhost:8000

Use credentials to login application
- username : test@test.com
- password : testpassword

EER diagram can find in
- /backend/docs/er/er.mmd

Api doc can find in
- /backend/docs/openapi.yaml

.cursor/rules
- Instructions for coding and planning agent

.cursor/plans
- Planing docs used to develop application


