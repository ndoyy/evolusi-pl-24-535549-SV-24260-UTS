FROM dunglas/frankenphp

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install unzip dan ekstensi zip PHP (wajib untuk composer install)
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy file composer terlebih dahulu untuk Layer Caching
COPY composer.json composer.lock ./

# Install dependensi tanpa dev
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy seluruh kode aplikasi
COPY . .

# HAPUS CACHE LARAVEL (PENTING - agar tidak refer ke Pail/dev packages)
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

# Set permission untuk folder storage dan cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port yang digunakan FrankenPHP
EXPOSE 8000

# Perintah menjalankan server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]