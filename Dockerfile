FROM node:lts-alpine

# install simple http server for serving static content
RUN npm install -g http-server

# make the 'app' folder the current working directory
WORKDIR /app

# copy project files and folders to the current working directory (i.e. 'app' folder)
COPY board /app

# install project dependencies
RUN npm install

# build app for production with minification
RUN npm run build

EXPOSE 8080
CMD [ "http-server", "dist" ]


FROM php:8.1-apache
WORKDIR /app
COPY back/ /app
RUN docker-php-ext-install mysqli