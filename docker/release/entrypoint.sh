#!/bin/sh
set -e  # Exit immediately if any command fails

INSTANCE=${CONTAINER_ROLE:-web}

echo "[Entrypoint] Starting $INSTANCE..."

# Force resolve service names to IPs and append to /etc/hosts.
# This bypasses the Alpine DNS resolver which often times out (1s) on IPv6.
echo "[Entrypoint] Patching /etc/hosts for performance..."
if getent hosts db > /dev/null; then
    getent hosts db | awk '{ print $1 " db" }' >> /etc/hosts
    echo "   -> Mapped 'db' to $(getent hosts db | awk '{ print $1 }')"
fi

if getent hosts redis > /dev/null; then
    getent hosts redis | awk '{ print $1 " redis" }' >> /etc/hosts
    echo "   -> Mapped 'redis' to $(getent hosts redis | awk '{ print $1 }')"
fi

if [ "$#" -gt 0 ]; then
    echo "you can use artisan via 'production <command>'"
    exec "$@"
fi

if [ "$INSTANCE" = "web" ]; then
    echo "[Entrypoint] Updating Cloudflare IP ranges..."
    if CF_IPS=$( { wget -qO- https://www.cloudflare.com/ips-v4; echo; wget -qO- https://www.cloudflare.com/ips-v6; } | tr '\n' ' ' ); then
        echo "trusted_proxies static private_ranges $CF_IPS" > /etc/caddy/trusted_proxies.caddy
        echo "   -> Cloudflare IPs updated."
    else
        echo "   -> [WARNING] Failed to fetch Cloudflare IPs. Using default private ranges."
        echo "trusted_proxies static private_ranges" > /etc/caddy/trusted_proxies.caddy
    fi

    echo "[Entrypoint] Linking Storage..."
    /app/artisan storage:link --force

    echo "[Entrypoint] Starting FrankenPHP..."
    exec php -d variables_order=EGPCS /app/artisan octane:start --server=frankenphp --host=0.0.0.0 --admin-port=2019 --port=80 --max-requests=500 --caddyfile=/etc/caddy/Caddyfile

elif [ "$INSTANCE" = "worker" ]; then
    echo "[Entrypoint] Starting Laravel Worker..."
    exec /app/artisan queue:work --name=queue-worker --queue=default --sleep=3 --tries=3 --max-time=3600 --json

elif [ "$INSTANCE" = "scheduler" ]; then
    echo "[Entrypoint] Starting Laravel Scheduler..."
    exec /app/artisan schedule:work --whisper

elif [ "$INSTANCE" = "init" ]; then
    echo "[Entrypoint] Running Initializations..."
    if [ -f /app/init-app.sh ]; then
        /bin/sh /app/init-app.sh
    else
        echo "[Entrypoint] /app/init-app.sh not found."
    fi
    echo "[Entrypoint] Initialization complete."
    exit 0

else
    echo "[Entrypoint] Error: Unknown Instance '$INSTANCE'"
    exit 1
fi
