#!/usr/bin/env sh
set -e

APP_DIR=/app
INSTALLED_FILE=$APP_DIR/INSTALLED

echo "Ensuring writable directories"

mkdir -p runtime web/assets web/uploads
chown -R www-data:www-data runtime web/assets web/uploads
chmod -R 775 runtime web/assets web/uploads

echo "Waiting for MySQL..."

until php -r "
try {
    new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASSWORD'));
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
"
do
  sleep 2
done

echo "MySQL is ready"

if [ -f "$INSTALLED_FILE" ]; then
  echo "ℹ Application already installed — skipping bootstrap"
  exit 0
fi

echo "Installing composer dependencies..."
composer install --no-interaction --prefer-dist

echo "Fixing permissions..."
chown -R www-data:www-data runtime web/assets

echo "Running migrations..."
php yii migrate --interactive=0

touch INSTALLED
echo "Installation complete."
