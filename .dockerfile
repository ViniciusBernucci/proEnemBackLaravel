FROM php:8.2-fpm

# Dependências necessárias para compilar as extensões
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    build-essential \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    libxml2-dev \
    libonig-dev \
    pkg-config \
  && rm -rf /var/lib/apt/lists/*

# Extensões PHP
RUN docker-php-ext-install -j"$(nproc)" \
    pdo_pgsql \
    zip \
    mbstring \
    exif \
    pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
EXPOSE 9000
CMD ["php-fpm"]
