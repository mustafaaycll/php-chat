FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite \
    && echo "listen = 9000" > /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /app

# Copy composer files first and install dependencies
COPY composer.json composer.lock ./
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

COPY src/ src/
COPY public/ public/
COPY scripts/init.sh /app/init.sh

RUN chmod +x /app/init.sh && mkdir -p /app/data && chmod -R 777 /app/data

CMD ["/app/init.sh"]