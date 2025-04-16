FROM php:8.3-fpm

WORKDIR /var/www

# System dependencies for Laravel + PDF generation
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    ghostscript \
    wkhtmltopdf \
    poppler-utils \
    libxrender1 \
    libfontconfig1 \
    libxext6 \
    && docker-php-ext-install pdo mbstring zip gd

# Increase memory limit for heavy PDF generation
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Composer install
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app files
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
