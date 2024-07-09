# Stage 1: Build the Vue.js app
FROM node:lts-alpine AS build
WORKDIR /app
COPY board/ /app
RUN npm install
RUN npm run build

# Stage 2: Setup the PHP app with Apache
FROM php:8.1-apache
WORKDIR /var/www/html

# Copy PHP files to Apache document root
COPY back/ /var/www/html/

# Copy built Vue.js app to Apache document root
COPY --from=build /app/dist /var/www/html/board

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Configure Apache to serve both Vue.js and PHP
RUN echo "ServerName localhost\n\
Alias /board /var/www/html/board\n\
<Directory /var/www/html/board>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
\n\
<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/app.conf && \
    a2enconf app && \
    a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
