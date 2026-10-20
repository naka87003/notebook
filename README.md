# Notebook

Laravel application for creating, editing, searching, and sharing notes.

- Production: https://notebook.naka87003.com/
- Backend: Laravel
- Frontend: Vue, Inertia.js, Vuetify

## Project structure

```text
src/                  Laravel application
docker/               Local development container configuration
docker-compose.yml    Local development environment
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

The production environment runs on Laravel Cloud. Laravel Cloud deploys the application when changes are pushed to the branch configured for the production environment.

Configure production settings through Laravel Cloud resources and Custom Environment Variables. Do not store credentials in this repository or in variables whose names start with `VITE_`, because Vite exposes them to the browser.

The production environment uses the following Laravel Cloud resources:

- MySQL database for application data and database-backed queues
- Private object-storage bucket for note and profile images
- Resend email resource for transactional email

Run a long-running process for the database queue worker:

```bash
php artisan queue:work database --sleep=10
```

The App environment must remain awake while this self-managed queue worker is used.
