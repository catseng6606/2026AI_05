FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY public/ /var/www/html/
