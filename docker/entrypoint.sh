#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Block until the database answers, so migrations never race the connection.
# The probe reports why it failed: a bare "unreachable" is indistinguishable
# from bad credentials, a wrong host, or a network policy drop.
db_probe() {
    php -r '
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;sslmode=%s",
            getenv("DB_HOST") ?: "",
            getenv("DB_PORT") ?: "5432",
            getenv("DB_DATABASE") ?: "postgres",
            getenv("DB_SSLMODE") ?: "prefer"
        );
        try {
            $pdo = new PDO(
                $dsn,
                getenv("DB_USERNAME") ?: "",
                getenv("DB_PASSWORD") ?: "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->query("select 1");
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage().PHP_EOL);
            exit(1);
        }
    ' 2>&1
}

echo "==> Waiting for database..."
last_error=""
for attempt in $(seq 1 30); do
    if output=$(db_probe); then
        echo "==> Database reachable"
        break
    else
        last_error="$output"
    fi

    if [ "$attempt" -eq 30 ]; then
        {
            echo "!! Database unreachable after 30 attempts (60s)"
            echo "   Connection target:"
            echo "     host    = ${DB_HOST:-<UNSET>}"
            echo "     port    = ${DB_PORT:-5432}"
            echo "     dbname  = ${DB_DATABASE:-<UNSET>}"
            echo "     user    = ${DB_USERNAME:-<UNSET>}"
            echo "     sslmode = ${DB_SSLMODE:-prefer}"
            if [ -n "${DB_PASSWORD:-}" ]; then
                echo "     password= <set>"
            else
                echo "     password= <UNSET>"
            fi
            echo "   Driver error: $last_error"
        } >&2
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