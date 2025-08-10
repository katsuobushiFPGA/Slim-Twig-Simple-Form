#!/bin/bash

echo "=== PHP Container Starting ==="

# composer.jsonが存在し、vendorディレクトリが存在しない場合はcomposer installを実行
if [ -f /var/www/html/composer.json ] && [ ! -d /var/www/html/vendor ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader
    echo "Composer dependencies installed successfully!"
fi

# composer.jsonが存在し、composer.lockが更新されている場合はcomposer installを実行
if [ -f /var/www/html/composer.json ] && [ -f /var/www/html/composer.lock ]; then
    cd /var/www/html
    if [ /var/www/html/composer.lock -nt /var/www/html/vendor/autoload.php ] 2>/dev/null; then
        echo "Updating Composer dependencies..."
        composer install --no-dev --optimize-autoloader
        echo "Composer dependencies updated successfully!"
    fi
fi

echo "Starting PHP-FPM..."

# 元のコマンドを実行
exec "$@"
