FROM php:8.3-fpm-alpine3.19

#RUN docker-php-ext-install ldap

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions; \
    install-php-extensions ldap 

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    sed -i 's/^;\?\(cgi\.fix_pathinfo\)=[0-1].*/\1=0/' "$PHP_INI_DIR/php.ini"; \
    mkdir /logs && chown www-data:www-data /logs

USER www-data

