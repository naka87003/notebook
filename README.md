# Notebook

Laravel application for creating, editing, searching, and sharing notes.

- Demo: https://xs257355.xsrv.jp/
- Backend: Laravel
- Frontend: Vue, Inertia.js, Vuetify

## 構成

```text
src/                  Laravel application
docker/               Local development container configuration
docker-compose.yml    Local development environment
.github/workflows/    Automated deployment to Xserver
```

## Local development

```bash
cp src/.env.example src/.env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app npm ci
docker compose exec app npm run dev
```

The application is available at http://localhost:8000.

## Deployment

When changes under `src/` are pushed to the `main` branch, GitHub Actions automatically deploys the application to Xserver.

Production connection details are stored in the GitHub Actions `production` environment. The `.env` file and `storage` directory are managed on the server and are not included in this repository.
