FROM php:8.4-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends git unzip && rm -rf /var/lib/apt/lists/*
COPY --from=ghcr.io/mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql pdo_sqlite pcntl bcmath zip intl gd exif opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
 && (php artisan package:discover --ansi || true)
ENV PHP_CLI_SERVER_WORKERS=4 APP_DIR=/app
EXPOSE 8001
COPY docker-entrypoint.sh /usr/local/bin/pluff-entrypoint
RUN chmod +x /usr/local/bin/pluff-entrypoint
ENTRYPOINT ["pluff-entrypoint"]
CMD ["php","artisan","serve","--host=0.0.0.0","--port=8001"]
