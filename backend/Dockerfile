FROM dunglas/frankenphp

# Set working directory
WORKDIR /app

# Copy file composer terlebih dahulu untuk Layer Caching
COPY composer.json composer.lock ./

# Install dependensi tanpa dev (untuk production)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy seluruh kode aplikasi
COPY . .

# Set permission untuk folder storage dan cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port yang digunakan FrankenPHP
EXPOSE 8000

# Perintah menjalankan server
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]