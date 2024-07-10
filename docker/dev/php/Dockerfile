FROM php:8.3-fpm

SHELL ["/bin/bash", "-c"]
WORKDIR "/api/"

# Install OS libs.
RUN apt update -q && \
    apt install -qy \
    curl \
    libpq-dev \
    libicu-dev \
    git && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP related libs.
RUN apt update -q && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    docker-php-ext-install pdo pdo_pgsql intl opcache

# Configure xdebug.
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install php-cs-fixer.
RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v2.phar -o php-cs-fixer && \
    chmod +x php-cs-fixer && \
    mv php-cs-fixer /usr/local/bin/php-cs-fixer

# Copy conf files.
COPY ./docker/dev/php/php.ini /etc/php/8.3/cli/conf.d/50-setting.ini
COPY ./docker/dev/php/php.ini /etc/php/8.3/fpm/conf.d/50-setting.ini
COPY ./docker/dev/php/pool.conf /etc/php/8.3/fpm/pool.d/www.conf
COPY ./docker/dev/php/symfony.conf /etc/nginx/symfony.conf

# PHP configuration for file upload sizes
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/50-setting.ini
RUN echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/50-setting.ini

# Installation of composer and PHP dependencies.
COPY composer.json .
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-scripts --no-autoloader

# Install symfony cli.
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Add user for symfony application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Change current user to www.
USER www

EXPOSE 9000
ENTRYPOINT ["php-fpm"]
