# Versi PHP dikunci (bukan latest). 8.4.27 sudah memuat patch CVE-2026-109618 dan CVE-2026-109619.
FROM php:8.4.27-cli-bookworm

# Composer dengan versi yang jelas
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# unzip dan ekstensi PHP yang dibutuhkan (zip untuk composer, pdo_mysql untuk database)
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Salin composer.json dan composer.lock lebih dulu agar layer dependensi bisa di-cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Baru salin kode aplikasi
COPY . .

# Bangun ulang autoload untuk kode aplikasi, hapus cache Laravel yang merujuk paket dev,
# lalu atur izin folder storage dan cache
RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]