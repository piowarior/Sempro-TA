FROM php:8.4-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    curl \
    git \
    tzdata \
    linux-headers \
    mysql-client \
    && docker-php-ext-install pdo_mysql bcmath pcntl

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
