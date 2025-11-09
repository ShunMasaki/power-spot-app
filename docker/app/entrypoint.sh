#!/bin/bash
set -e

# データベース接続を待つ（最大30秒）
echo "Waiting for database connection..."
for i in {1..30}; do
    php artisan migrate:status > /dev/null 2>&1 && break
    echo "Attempt $i/30: Database not ready, waiting..."
    sleep 1
done

# マイグレーションを実行
echo "Running migrations..."
php artisan migrate --force

# シーダーを実行（初回のみ）
SEED_FLAG_FILE="/var/www/html/storage/app/.seed_completed"
if [ ! -f "$SEED_FLAG_FILE" ]; then
    echo "Running seeders..."
    php artisan db:seed --force
    touch "$SEED_FLAG_FILE"
    echo "Seeders completed."
else
    echo "Seeders already completed. Skipping..."
fi

# PHP-FPMを起動
exec php-fpm

