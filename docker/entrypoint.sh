#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Block until the database answers, so migrations never race the connection.
echo "==> Waiting for database..."
for attempt in $(seq 1 30); do
    if php -r '
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST"),
            getenv("DB_PORT") ?: 5432,
            getenv("DB_DATABASE") ?: "postgres"
        );
        try {
            $pdo = new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->query("select 1");
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    ' 2>/dev/null; then
        echo "==> Database reachable"
        break
    fi

    if [ "$attempt" -eq 30 ]; then
        echo "!! Database unreachable after 30 attempts (60s)" >&2
        exit 1
    fi

    sleep 2
done

# Environment variables are injected at runtime, so drop anything cached
# into the image at build time.
php artisan config:clear --no-interaction

# Migrations run on every container start, including each wake from Render's
# free-tier sleep. migrate --force is idempotent, so this is safe to repeat.
echo "==> Running migrations"
php artisan migrate --force --no-interaction

# Only meaningful while profile images are still on the local disk. Once the
# profile_images disk points at Supabase Storage this is a no-op.
php artisan storage:link --force --no-interaction >/dev/null 2>&1 || true

exec "$@"