FROM dunglas/frankenphp:php8.5-alpine AS base
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
ENV TZ=Europe/Berlin

# Install PHP extensions
RUN install-php-extensions \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache \
    pdo_pgsql \
    redis \
    zip

RUN apk add --no-cache \
    supervisor \
    && rm -rf /var/cache/apk/*

# Dependencies
FROM base AS build

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-autoloader \
    --no-scripts

COPY . /app

# Generate Optimized Autoloader
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative && rm /usr/bin/composer

FROM base AS runner

LABEL maintainer="VHeart IT Team" description="VHeart Website"

ENV APP_ENV=production
COPY --from=build /app /app

# Setup Permissions
RUN mkdir -p /var/log/supervisor storage/framework/sessions storage/framework/views storage/framework/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy Configs
COPY docker/release/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/release/Caddyfile /etc/caddy/Caddyfile
COPY docker/release/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/release/init.sh /app/init-app.sh
COPY docker/release/entrypoint.sh /app/entrypoint.sh

# Setup Caddy Proxies
RUN echo "trusted_proxies static private_ranges" > /etc/caddy/trusted_proxies.caddy

# Make scripts executable
RUN chmod +x /app/init-app.sh /app/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/app/entrypoint.sh"]
