FROM php:8.2-cli

RUN apt-get update && apt-get install -y git unzip libpng-dev libonig-dev libxml2-dev libsqlite3-dev zip curl \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chmod -R 775 storage bootstrap/cache

ENV PORT=10000
EXPOSE 10000
CMD ["sh", "docker/entrypoint.sh"]
