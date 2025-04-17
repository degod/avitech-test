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
    poppler-utils \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo zip gd xml bcmath

# Create a dedicated runtime dir for www-data
RUN mkdir -p /var/www-runtime && chown www-data:www-data /var/www-runtime
ENV XDG_RUNTIME_DIR=/var/www-runtime

# Ensure permissions on tmp
RUN chmod 1777 /tmp

# Verify wkhtmltopdf installation
RUN wkhtmltopdf --version

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app source
COPY . .

# Copy php.ini into the container
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]