#!/bin/bash

echo "=== PHP Container Starting ==="
echo "APP_ENV: ${APP_ENV:-production}"
echo "INSTALL_DEV_DEPS: ${INSTALL_DEV_DEPS:-false}"

# composer.jsonが存在し、vendorディレクトリが存在しない場合はcomposer installを実行
if [ -f /var/www/html/composer.json ] && [ ! -d /var/www/html/vendor ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    
    # 環境変数に基づいて開発依存関係の有無を決定
    if [ "${INSTALL_DEV_DEPS:-false}" = "true" ]; then
        echo "Installing with dev dependencies..."
        composer install --optimize-autoloader
    else
        echo "Installing without dev dependencies..."
        composer install --no-dev --optimize-autoloader
    fi
    echo "Composer dependencies installed successfully!"
fi

# composer.jsonが存在し、composer.lockが更新されている場合はcomposer installを実行
if [ -f /var/www/html/composer.json ] && [ -f /var/www/html/composer.lock ]; then
    cd /var/www/html
    if [ /var/www/html/composer.lock -nt /var/www/html/vendor/autoload.php ] 2>/dev/null; then
        echo "Updating Composer dependencies..."
        
        # 環境変数に基づいて開発依存関係の有無を決定
        if [ "${INSTALL_DEV_DEPS:-false}" = "true" ]; then
            echo "Updating with dev dependencies..."
            composer install --optimize-autoloader
        else
            echo "Updating without dev dependencies..."
            composer install --no-dev --optimize-autoloader
        fi
        echo "Composer dependencies updated successfully!"
    fi
fi

echo "Starting PHP-FPM..."

# 元のコマンドを実行
exec "$@"
