#!/bin/bash
set -e

echo "🚀 Starting Laravel application..."

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
while ! nc -z mysql 3306; do
    echo "   MySQL is unavailable - sleeping"
    sleep 2
done
echo "✅ MySQL is ready!"

# Always install/update composer dependencies
echo "📦 Installing composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Verify Ziggy is installed
if [ ! -d "vendor/tightenco/ziggy" ]; then
    echo "❌ Ziggy package not found! Installing..."
    composer require tightenco/ziggy
fi

# Generate app key if not exists
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
else
    echo "✅ Application key already exists"
fi

# Copy Ziggy Vue module for Vite
echo "📝 Copying Ziggy Vue module to resources/js..."
cp vendor/tightenco/ziggy/dist/index.esm.js resources/js/ziggy-vue.js

# Generate Ziggy routes
echo "🗺️  Generating Ziggy routes..."
php artisan ziggy:generate

# Ensure session table migration exists
echo "📊 Ensuring session table migration..."
php artisan session:table 2>/dev/null || true

# Check if migrations have been run
echo "🗄️  Checking database status..."
TABLES_COUNT=$(php artisan db:table users --count 2>/dev/null || echo "0")
if [ "$TABLES_COUNT" = "0" ]; then
    echo "   Running fresh migrations and seeders..."
    php artisan migrate:fresh --seed --force
else
    echo "   Running pending migrations..."
    php artisan migrate:fresh --seed
fi

# Fix permissions
echo "🔧 Fixing permissions..."
chown -R erp:erp /var/www 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

echo "✅ Laravel application ready!"
echo "🌐 Access the app at: http://localhost:8000"
echo ""

# Start supervisor and PHP-FPM
echo "🚀 Starting services..."
service supervisor start
exec php-fpm
