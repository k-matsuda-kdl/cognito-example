FROM php:8.4.1-apache

# php.ini や Apacheの設定ファイルをコピー
ADD ./app/php.ini /usr/local/etc/php/
ADD ./app/000-default.conf /etc/apache2/sites-enabled/

# Composerインストール
RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer

# 必要なライブラリをインストール
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl pdo_mysql zip bcmath gd \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# modRewriteなどの有効化
RUN mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled && /bin/sh -c a2enmod rewrite
RUN mv /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled && /bin/sh -c a2enmod headers

EXPOSE 80
