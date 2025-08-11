# PHP 8.4 with FPM
FROM php:8.4-fpm

# 環境変数の設定
ARG APP_ENV=production
ARG INSTALL_DEV_DEPS=false
ARG USER_ID=1000
ARG GROUP_ID=1000

# ビルド時にのみ使用（実行時環境変数はcompose.ymlで設定）

# ユーザーIDとグループIDを環境変数として設定（デフォルト値1000）
RUN usermod -u $USER_ID www-data \
    && groupmod -g $GROUP_ID www-data \
    && mkdir -p /home/www-data \
    && chown www-data:www-data /home/www-data \
    && usermod -d /home/www-data www-data

# 作業ディレクトリを設定
WORKDIR /var/www/html

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHPの設定を最適化
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/php.ini \
    && echo "upload_max_filesize = 32M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/php.ini

# エントリーポイントスクリプトをコピーして実行権限を付与
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# www-dataユーザーで実行
USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
