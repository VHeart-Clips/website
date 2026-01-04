FROM dunglas/frankenphp:php8.5-alpine AS base

WORKDIR /app

RUN install-php-extensions \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache \
    pdo_pgsql \
    redis \
    zip


FROM base AS builder

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

COPY . .

RUN rm -rf /app/docker /app/compose.yml
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

FROM base AS runner

LABEL maintainer="VHeart IT Team" version="1.0" description="VHeart Website"

# Install Runtime Dependencies
RUN apk add --no-cache \
    supervisor \
    ffmpeg \
    bash \
    curl

COPY --from=builder --chown=www-data:www-data /app /app

# Setup Permissions
RUN mkdir -p /var/log/supervisor \
    && chmod -R 775 storage bootstrap/cache

# Copy Configs
COPY docker/release/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/release/Caddyfile /etc/caddy/Caddyfile
COPY docker/release/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/release/init.sh /app/init-app.sh
COPY docker/release/entrypoint.sh /app/entrypoint.sh

RUN echo "trusted_proxies static private_ranges" > /etc/caddy/trusted_proxies.caddy

# Make scripts executable
RUN chmod +x /app/init-app.sh /app/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/app/entrypoint.sh"]
