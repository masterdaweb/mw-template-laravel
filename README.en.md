# Laravel on Master da Web

A Laravel 13 app wired to everything a real project needs, deployed in one click from the console's template gallery.

| Service | What it runs |
| --- | --- |
| `web` | the site, on FrankenPHP; `php artisan migrate --force` runs once per deploy, before traffic moves |
| `worker` | `php artisan queue:work`: the jobs your code dispatches |
| `scheduler` | `php artisan schedule:run`, every minute |

They share PostgreSQL (`DB_*`), Redis (`REDIS_*`: queue, cache and sessions) and a public Object Storage bucket (`AWS_*`, the `s3` disk), all injected by the project: nothing to copy by hand. `APP_KEY` was generated for you.

The home page shows each part working: send a file and it goes to Object Storage, gets a row in PostgreSQL and a job the worker processes; the scheduler writes a heartbeat to Redis every minute. `GET /status.json` gives the same checks for a monitor.

## Make it yours

1. In the console, open the project and follow **Your code**: copy this
   template to your GitHub and connect the copy to the project.
2. Change `routes/web.php` and `resources/views/welcome.blade.php`, then push: every push deploys, with no downtime.

## Run it on your machine

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve
```

## Good to know

- Files written to the container's disk are gone at the next deploy: keep uploads on the `s3` disk, as this app does.
- Adding Vite (`package.json`)? The build then caches the config; the worker and scheduler commands start with `php artisan config:clear` so they read the project's variables.

*[Português](README.md)*
