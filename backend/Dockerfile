FROM php:8.3-fpm-alpine3.19

#RUN docker-php-ext-install ldap

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

#ENV LANG fr _FR.UTF-8
#RUN apk add musl musl-utils musl-locales tzdata; \
#    ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime

RUN apk add --no-cache --update fcgi; \
    chmod +x /usr/local/bin/install-php-extensions; \
    install-php-extensions ldap 

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    sed -i 's/^;\?\(cgi\.fix_pathinfo\)=[0-1].*/\1=0/' "$PHP_INI_DIR/php.ini"; \
    mkdir /logs && chown www-data:www-data /logs
    #echo "[www]" > /usr/local/etc/php-fpm.d/90_fpm-status.conf; \
    #echo "pm.status_path /status" >> /usr/local/etc/php-fpm.d/90_fpm-status.conf; \
    #echo "pm.status_listen = 127.0.0.1:9001" >> /usr/local/etc/php-fpm.d/90_fpm-status.conf; \
    #echo "ping.path = /ping" >> /usr/local/etc/php-fpm.d/90_fpm-status.conf

USER www-data

