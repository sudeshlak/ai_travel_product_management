# AI Travel Product Management

Local setup guide for the full stack (Laravel API + React frontend) using Docker Compose.

## Prerequisites

Install these on your computer before starting:

- **Docker Desktop** (macOS / Windows) or **Docker Engine + Compose** (Linux)
- **MySQL 8.x** running on the host (Compose does not start a database container)
- **Git**

PHP, Composer, and Node.js are **not** required on the host when using Docker. The images already include PHP 8.4 and Node 22.

## Database

Create one MySQL database. Name and credentials must match `backend/.env`.

```sql
CREATE DATABASE ai_travel_product_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Default example credentials:

- Database: `ai_travel_product_management`
- Username: `root`
- Password: *(empty, or set to match your local MySQL)*

Redis is **not** required for the default local setup. Sessions, cache, and queues use the database driver.

## Environment variables

### Backend

```bash
cp backend/.env.example backend/.env
```

Update these values in `backend/.env`:

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

```bash
cp frontend/.env.example frontend/.env
```

Update `frontend/.env`:

| Variable | Purpose | Typical local value |
| --- | --- | --- |
| `VITE_API_BASE_URL` | Backend API base URL | `http://localhost:8000/api` |

## Run the project

From the repository root:

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
# Edit backend/.env (DB_* / OPENAI_*) and confirm frontend/.env

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

Stop with `Ctrl+C`, or:

```bash
docker compose down
```

## Languages, frameworks, and versions

### Backend

| Technology | Version |
| --- | --- |
| PHP | 8.4 |
| Laravel Framework | ^13.17 |
| Laravel Sanctum | ^4.3 |
| Composer | 2 |
| MySQL | 8.x (host) |

### Frontend

| Technology | Version |
| --- | --- |
| Node.js | 22 |
| React | ^19.2 |
| TypeScript | ~6.0 |
| Vite | ^8.2 |
| React Router | ^7.18 |
| Redux Toolkit | ^2.12 |
| React Redux | ^9.3 |
| redux-persist | ^6 |
| TanStack Query | ^5.102 |
| TanStack Table | ^8.21 |
| Bootstrap | ^5.3 |
| Axios | ^1.20 |
| Sass | ^1.104 |
| Oxlint | ^1.79 |
