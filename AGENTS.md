# Development commands

- Run application development commands inside the Docker Compose `app` container, not on the host. This includes dependency installation, npm scripts, Composer, Artisan, and tests.
- From the repository root, use `docker compose exec app <command>`, for example `docker compose exec app npm install` or `docker compose exec app npm test`.
- Docker Compose management, Git operations, and file inspection can run on the host. If the container is unavailable, report the limitation rather than silently running application commands on the host.
