# FROM php:7.2-apache
FROM php@sha256:34d20a90946b2a2e05ca6d2fe71f1394168af6dc1d18ab8211cc43f1679410cb
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN rm -rf /var/lib/apt/lists/*
RUN apt-get update && apt-get install -y libpq-dev zlib1g-dev wget unzip libpng-dev libgmp-dev
RUN docker-php-ext-install -j$(nproc) pgsql pdo_pgsql zip pdo_mysql gd gmp pcntl
RUN a2enmod rewrite

# "install" pg_dump (laravel backups require it)
# that's an ugly hack but apt-get install postgresql-client
# fails with dependency issues and this just seems to be working
RUN \
    apt-get update \
 && apt-get download postgresql-client-9.6 \
 && dpkg-deb -x postgresql-client-9.6* postgre \
 && cp postgre/usr/lib/postgresql/9.6/bin/pg_dump /usr/local/bin/pg_dump \
 && rm -rf postgresql-client-9.6* postgre

# install nodejs
# RUN apt-get install -y curl gnupg software-properties-common
# RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
# RUN apt-get install -y nodejs

# running  this before COPY . /var/www/html help with caching composer installation
COPY --chown=www-data:www-data install_composer.sh /var/www/html/
RUN /bin/sh install_composer.sh --install-dir=/usr/local/bin --filename=composer

COPY --chown=www-data:www-data . /var/www/html
#RUN npm install && npm run production

# Copy php .ini files
COPY docker_config/php/ /usr/local/etc/php/conf.d/
