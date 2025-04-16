FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    wget \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    xfonts-base \
    xfonts-75dpi \
    fontconfig \
    libxrender1 \
    libx11-6 \
    libxext6 \
    libssl-dev \
    wkhtmltopdf \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo zip gd xml bcmath

# Verify wkhtmltopdf installation
RUN wkhtmltopdf --version

# Increased memory limit
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memlimit.ini

# Set PHP upload limits
RUN echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app source
COPY . .

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]