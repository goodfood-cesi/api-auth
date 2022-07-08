FROM php:8.1-apache
WORKDIR /app

RUN apt-get update -qq && \
    apt-get install -qy \
    libzip-dev \
    git \
    unzip \
    nano \
    zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) zip opcache pdo_mysql
COPY .docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/apache.conf /etc/apache2/conf-available/z-app.conf
COPY .env.production .env
COPY . /app
RUN chown www-data:www-data /app -R

# Composer install
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite remoteip ssl && \
    a2enconf z-app
