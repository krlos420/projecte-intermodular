FROM php:8.4-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    libssl-dev \
    libjemalloc2 \
    $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Extensiones básicas de PHP
RUN docker-php-ext-install pdo pdo_mysql xml ctype zip

# Instalar extensión de MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Iniciar Xdebug
RUN docker-php-ext-enable xdebug

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

# Configuración PHP
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

# Crear un grupo con GID 1000
RUN groupadd -g 1000 app

# Crear usuario y añadirlo al grupo app
RUN useradd -u 1000 -g 1000 -m -s /bin/bash app
RUN chown -R app:app /var/www/html

# Directorio de trabajo
WORKDIR /var/www/html