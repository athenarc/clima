# Start with official yii2 image because it contains all dependencies

FROM yiisoftware/yii2-php:7.4-apache

RUN apt update && \
    apt install -y libyaml-dev \
    apache2 \
    postgresql-client \
    cron

RUN mkdir /app/web

WORKDIR /app/web

# Update composer to version 2 because the image contains version 1
RUN composer self-update --2 && \
# Install yii2
    composer create-project --prefer-dist yiisoft/yii2-app-basic clima

WORKDIR /app/web/clima

# Change the composer minimum stability setting
RUN sed -i "s|\"minimum-stability\": \"stable\"|\"minimum-stability\": \"dev\" |g" composer.json && \
# Change the default location for apache
    sed -i "s|DocumentRoot /app/web|DocumentRoot /app/web/clima/web |g" /etc/apache2/sites-available/000-default.conf && \
# Increase php post/file upload limit and restart apache
    sed -i "s|upload_max_filesize = 2M|upload_max_filesize = 50G |g" /usr/local/etc/php/php.ini-production && \
    sed -i "s|post_max_size = 8M|post_max_size = 50G |g" /usr/local/etc/php/php.ini-production && \
    sed -i "s|display_errors = Off|display_errors = On |g" /usr/local/etc/php/php.ini-production && \
    sed -i "s|max_execution_time = 30|max_execution_time = 1000 |g" /usr/local/etc/php/php.ini-production && \
    echo "error_log = /dev/stderr" >> /usr/local/etc/php/php.ini-production && \
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
# Since OpenShift annot listen to <1024, we'll use port 8080 (thanks Alvaro Gonzalez!)
    sed -i "s|Listen 80|Listen 8080|" /etc/apache2/ports.conf && \
    sed -i "s|<VirtualHost \*:80>|<VirtualHost *:8080>|" /etc/apache2/sites-available/000-default.conf

# Install required yii2 plugins
RUN composer require webvimark/module-user-management && \
    composer require kartik-v/yii2-widget-datepicker "dev-master" && \
    composer require --prefer-dist yiisoft/yii2-bootstrap4 && \
    composer require --prefer-dist yiisoft/yii2-bootstrap && \
    composer require --prefer-dist yiisoft/yii2-httpclient && \
    composer require 2amigos/yii2-ckeditor-widget && \
    composer require --prefer-dist yiisoft/yii2-swiftmailer && \
    composer require yiisoft/yii2-jui && \
    composer require kartik-v/yii2-widget-datepicker "@dev" && \
    composer require kartik-v/yii2-field-range "dev-master"  && \
    composer require "rmrevin/yii2-fontawesome:2.10.*"

RUN mkdir /data && chmod 777 /data
USER www-data

RUN crontab -l | { cat; echo "00 12 * * *  php  /app/web/clima/yii cron-job/yesterday >> /data/cronjob.log 2>&1"; } | crontab -

USER root

COPY . /app/web/clima

#start apache2
CMD ["/bin/sh","-c", "cron && apache2-foreground"]

