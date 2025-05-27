# Base image PHP 8.1 con Apache
FROM php:8.1-apache

# Evitar prompts interactivos
ARG DEBIAN_FRONTEND=noninteractive

# Actualizar e instalar dependencias PHP y del sistema
RUN apt-get update && apt-get install -y \
    sendmail \
    libpng-dev \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    git \
    unzip \
    nodejs npm \
    && docker-php-ext-install -j$(nproc) mysqli mbstring zip gd dom xml pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Actualizar npm a la versión 8
RUN npm install -g npm@8

# Crear directorio de trabajo y copiar archivos
WORKDIR /var/www/html

COPY www /var/www/html

# Ajustar permisos correctos para Apache y Laravel
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar configuración personalizada de Apache
COPY conf/apache.conf /etc/apache2/sites-available/000-default.conf

# Exponer el puerto 80 para Apache
EXPOSE 80

# No ejecutar composer install ni npm install en el build,
# los ejecutarás dentro del contenedor tras levantarlo, para evitar rebuilds largos.

