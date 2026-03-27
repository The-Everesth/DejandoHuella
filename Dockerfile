FROM php:8.3-cli

# Instalar dependencias del sistema + node
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install zip pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear carpeta de trabajo
WORKDIR /var/www

# Copiar archivos
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias frontend
RUN npm install

# Build de producción
RUN npm run build

# Permisos
RUN chmod -R 775 storage bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Crear y dar permisos a logs/cache
RUN mkdir -p storage/logs bootstrap/cache && chmod -R 777 storage bootstrap/cache

# Ejecutar Laravel
CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan view:clear && \
    php artisan route:clear && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000}