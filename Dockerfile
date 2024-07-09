# Stage 1: Build the Vue.js app
FROM node:16 AS build
WORKDIR /app
COPY board/ /app
RUN npm install
RUN npm run build

# Stage 2: Setup the PHP app
FROM php:8.1-apache AS php
WORKDIR /var/www/html
COPY back/ /var/www/html
RUN docker-php-ext-install mysqli

# Stage 3: Serve the built Vue.js app and run PHP
FROM php:8.1-apache
WORKDIR /var/www/html

# Copy built Vue.js app to Apache document root
COPY --from=build /app/dist /var/www/html/board

# Copy PHP files to Apache document root
COPY --from=php /var/www/html /var/www/html/back

# Configure Apache to serve both Vue.js and PHP
RUN echo "Alias /board /var/www/html/board\n\
<Directory /var/www/html/board>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
\n\
<Directory /var/www/html/back>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/app.conf && \
    a2enconf app && \
    a2enmod rewrite

# Expose port 80
EXPOSE 80
CMD ["apache2-foreground"]
