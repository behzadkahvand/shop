FROM php:8.0-fpm-alpine
WORKDIR /var/www
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN apk add coreutils gmp-dev libpng-dev openssl-dev libxml2-dev shadow ;\
 usermod -u 1000 www-data ;\
 groupmod -g 1000 www-data ;\
 COMPOSER_SUM="$(wget -q -O - https://composer.github.io/installer.sig)" ;\
 wget -q -O /var/www/composer-setup.php https://getcomposer.org/installer ;\
 echo "$COMPOSER_SUM composer-setup.php" > composer-setup.php.sha384 ;\
 sha384sum -c /var/www/composer-setup.php.sha384 || exit 1;\
 php composer-setup.php --quiet --filename=composer --install-dir=/bin

RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install mongodb; echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/docker-php-ext-mongodb.ini

RUN docker-php-ext-install bcmath exif gd gmp pdo pdo_mysql soap zip ;\
  docker-php-ext-enable soap
RUN apk add libzip-dev; docker-php-ext-install zip 
RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN apk add rabbitmq-c rabbitmq-c-dev;\
    mkdir -p /usr/src/php/ext/amqp ;\
    curl -fsSL https://pecl.php.net/get/amqp | tar xvz -C "/usr/src/php/ext/amqp" --strip 1 ;\
    docker-php-ext-install amqp
COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini
