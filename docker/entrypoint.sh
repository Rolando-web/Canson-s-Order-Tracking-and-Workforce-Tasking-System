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
            $message = $e->getMessage();

            // Retry is only worthwhile for transient failures. A rejected
            // password or an open circuit breaker will not fix itself within
            // this loop, and every further attempt deepens the lockout.
            $permanent = str_contains($message, "ECIRCUITBREAKER")
                || str_contains($message, "SQLSTATE[28000]")
                || str_contains($message, "SQLSTATE[28P01]")
                || str_contains($message, "password authentication failed");

            fwrite(STDERR, ($permanent ? "PERMANENT " : "TRANSIENT ").$message.PHP_EOL);
            exit($permanent ? 2 : 1);
        }
    ' 2>&1
}

describe_connection() {
    {
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
}

echo "==> Waiting for database..."
last_error=""
for attempt in $(seq 1 30); do
    status=0
    output=$(db_probe) || status=$?

    if [ "$status" -eq 0 ]; then
        echo "==> Database reachable"
        break
    fi

    last_error="$output"

    # Exit 2 means the failure will not resolve itself: a rejected password
    # or an open circuit breaker. Bail immediately rather than spending 30
    # more attempts against a locked-out endpoint, which is what tripped the
    # breaker in the first place.
    if [ "$status" -eq 2 ]; then
        echo "!! Database rejected the connection; retrying will not help" >&2
        describe_connection
        exit 1
    fi

    if [ "$attempt" -eq 30 ]; then
        echo "!! Database unreachable after 30 attempts (60s)" >&2
        describe_connection
        exit 1
    fi

    sleep 2
done

# Environment variables are injected at runtime, so drop anything cached
# into the image at build time.
php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

# Migrations run on every container start, including each wake from Render's
# free-tier sleep. migrate --force is idempotent, so this is safe to repeat.
echo "==> Running migrations"
php artisan migrate --force --no-interaction

# Seeding is opt-in. Render's free tier has no shell, so there is no way to run
# this by hand on the running container; set SEED_ON_DEPLOY=true, deploy once,
# then unset it.
#
# Deliberately not run on every boot. DatabaseSeeder uses updateOrCreate with a
# hardcoded password, so every run resets the seeded accounts' credentials.
# Matching case variants so a capitalised "True" is not silently ignored.
case "${SEED_ON_DEPLOY:-false}" in
    true|TRUE|True|yes|YES|Yes|1)
        echo "==> Running database seeder"
        php artisan db:seed --force --no-interaction
        ;;
esac

# Only meaningful while profile images are still on the local disk. Once the
# profile_images disk points at Supabase Storage this is a no-op.
php artisan storage:link --force --no-interaction >/dev/null 2>&1 || true

# Build the framework caches, as the last step before serving traffic. This is
# the single largest win available without touching application code:
#
#   config:cache  - collapses ~50 config/*.php files into one array that is
#                   unserialised instead of parsed on every request.
#   route:cache   - collapses the route table into one file, so the router
#                   stops touching routes/web.php for every URL. This is why
#                   the root route became a controller method; a closure
#                   cannot be serialised and route:cache would throw.
#   view:cache    - precompiles every Blade template, so no view is parsed and
#                   written to storage/framework/views while serving traffic.
#
# Clearing happens first, above, because a cache file baked into the image at
# build time would hold build-time environment values rather than the ones
# Render injects. Running last means every artisan call above this block sees
# uncached config, so there is no ordering hazard between migrate/seed and the
# cache that is about to be written.
#
# Each step is individually non-fatal. This script runs under `set -e`, so a
# bare failure here would abort the boot and leave Render crash-looping the
# container. These caches are pure optimisations: without them the app still
# serves traffic correctly, just more slowly. route:cache in particular throws
# if any route is ever defined as a closure, and that should degrade to "no
# route cache" rather than take the site offline.
echo "==> Caching config, routes and views"
php artisan config:cache --no-interaction || echo "!! config:cache failed; continuing without it" >&2
php artisan route:cache  --no-interaction || echo "!! route:cache failed; continuing without it" >&2
php artisan view:cache   --no-interaction || echo "!! view:cache failed; continuing without it" >&2

# This script runs as root, so the files just written are root-owned and
# unreadable-in-practice by the www-data pool that will serve them. The
# Dockerfile pre-chowns these paths, but newly created files need the same
# treatment or php-fpm workers fail to open the config cache.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "==> Ready"

exec "$@"
