FROM dunglas/frankenphp:php8.5-alpine AS base

RUN install-php-extensions \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache \
    pdo_pgsql \
    redis \
    zip

RUN apk add --no-cache postgresql-client

WORKDIR /app
ENV TZ=Europe/Berlin \
    APP_ENV=production \
    APP_DEBUG=false

FROM base AS build

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/root/.composer/cache \
    composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && php artisan translations:export \
    && rm -f bootstrap/cache/*.php \
    && rm -rf storage/framework/cache/data/* \
    && rm -rf storage/framework/views/*.php \
    && rm -rf storage/framework/sessions/* \
    && rm -rf storage/framework/cache/data/*

RUN php artisan optimize \
    && php artisan config:clear

FROM base AS runner

LABEL maintainer="VHeart IT Team" description="VHeart Website"

ARG RELEASE=local
ENV SENTRY_RELEASE=$RELEASE

COPY --from=build /app /app

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache \
    && echo "trusted_proxies static private_ranges" > /etc/caddy/trusted_proxies.caddy \
    && echo "client_ip_headers X-Real-IP X-Forwarded-For" >> /etc/caddy/trusted_proxies.caddy


COPY docker/release/Caddyfile /etc/caddy/Caddyfile
COPY docker/release/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/release/init.sh /app/init-app.sh
COPY docker/release/entrypoint.sh /app/entrypoint.sh

RUN ln -s /app/artisan /usr/local/bin/production \
    && echo 'alias production="php /app/artisan"' >> /etc/profile \
    && echo 'export ENV=/etc/profile' >> /etc/environment \
    && chmod +x /app/init-app.sh /app/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/app/entrypoint.sh"]
