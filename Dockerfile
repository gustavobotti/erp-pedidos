FROM php:8.3-fpm

# Argumentos
ARG user=erp
ARG uid=1000

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip opcache

# Instalar Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar arquivos de configuração customizados do PHP
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Mudar para o usuário criado
USER $user
