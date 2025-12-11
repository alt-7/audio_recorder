#!/bin/bash

set -e

mkdir -p /var/www/html/web/audio-records
mkdir -p /var/www/html/runtime

chmod -R 777 /var/www/html/web/audio-records
chmod -R 777 /var/www/html/runtime
chmod 777 /var/www/html/web

echo "Проверка зависимостей Composer..."
composer install --prefer-dist --no-interaction --optimize-autoloader

echo "Ожидание базы данных..."
until php -r "if(@fsockopen('db', 5432)){exit(0);}else{exit(1);}"; do
  echo "База данных недоступна, ждем 1 сек..."
  sleep 1
done
echo "База данных готова!"

echo "Выполнение миграций..."
php yii migrate --interactive=0

echo "Генерация документации Swagger..."
php yii swagger/generate

echo "Запуск сервера..."
exec "$@"