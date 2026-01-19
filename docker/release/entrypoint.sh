#!/bin/sh
set -e  # Exit immediately if any command fails

echo "[Entrypoint] Starting Application Initialization..."

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

echo "[Entrypoint] Updating Cloudflare IP ranges..."
if CF_IPS=$( { wget -qO- https://www.cloudflare.com/ips-v4; echo; wget -qO- https://www.cloudflare.com/ips-v6; } | tr '\n' ' ' ); then
    echo "trusted_proxies static private_ranges $CF_IPS" > /etc/caddy/trusted_proxies.caddy
    echo "   -> Cloudflare IPs updated."
else
    echo "   -> [WARNING] Failed to fetch Cloudflare IPs. Using default private ranges."
    echo "trusted_proxies static private_ranges" > /etc/caddy/trusted_proxies.caddy
fi

# Run your existing initialization script
if [ -f /app/init-app.sh ]; then
    /bin/sh /app/init-app.sh
else
    echo "[Entrypoint] /app/init-app.sh not found. Skipping custom init."
fi

echo "[Entrypoint] Initialization complete."
echo "[Entrypoint] Starting Supervisor..."

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
