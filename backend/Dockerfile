FROM php:8.3-fpm

WORKDIR /var/www

# 🧩 Instala dependências de sistema
RUN apt-get update && apt-get install -y \
    git zip unzip curl autoconf make gcc g++ pkg-config \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd mbstring zip exif pcntl bcmath opcache

# 🐞 Instala o Xdebug (opcional, para debug local)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# 🧰 Copia o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 📦 Copia o código-fonte
COPY . .

# 🔧 Instala dependências do Laravel
RUN composer install --no-scripts --no-interaction

CMD ["php-fpm"]
