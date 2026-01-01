FROM php:8.5-fpm-alpine3.21

# fix car "docker-php-ext-install ldap" ne fonctionne pas
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk add --no-cache --update fcgi; \
    chmod +x /usr/local/bin/install-php-extensions; \
    install-php-extensions ldap 

# configuration de php
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    sed -i 's/^;\?\(cgi\.fix_pathinfo\)=[0-1].*/\1=0/' "$PHP_INI_DIR/php.ini"; \
    mkdir /logs && chown www-data:www-data /logs

COPY --chown=www-data:www-data . /var/www/html 

USER www-data
