FROM dunglas/frankenphp:php8.5-alpine AS base

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

# Dependencies
FROM base AS deps

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-autoloader \
    --no-scripts

FROM base AS runner

LABEL maintainer="VHeart IT Team" version="1.0" description="VHeart Website"

ENV APP_ENV=production

# Install Runtime Dependencies
RUN apk add --no-cache \
    supervisor \
    ffmpeg \
    bash \
    curl \
    && rm -rf /var/cache/apk/*

# Copy dependencies from the build stage
COPY --from=deps /app/vendor /app/vendor
COPY --from=deps /app/composer.json /app/composer.lock /app/
COPY ./public/build /app/public/build

# Copy Application
COPY . /app

# Generate Optimized Autoloader
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

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
