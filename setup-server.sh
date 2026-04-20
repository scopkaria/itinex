#!/bin/bash
# ============================================================
# Itinex - First-time Server Setup Script for Namecheap cPanel
# ============================================================
# Run this ONCE after creating the database in cPanel.
#
# BEFORE running this script:
# 1. Log into cPanel -> MySQL Databases
# 2. Create database: twnceosl_itinex
# 3. Create user: twnceosl_itinex (set a strong password)
# 4. Add user to database with ALL PRIVILEGES
# 5. Note down the password you set
#
# Then SSH into the server and run:
#   bash setup-server.sh YOUR_DB_PASSWORD
# ============================================================

set -e

DB_PASSWORD="${1}"

if [ -z "$DB_PASSWORD" ]; then
    echo "Usage: bash setup-server.sh YOUR_DB_PASSWORD"
    exit 1
fi

echo "==> Cloning repository..."
cd ~
if [ -d "itinex" ]; then
    echo "    ~/itinex already exists, pulling latest..."
    cd itinex && git pull origin main
else
    git clone https://github.com/scopkaria/itinex.git itinex
    cd itinex
fi

echo "==> Setting up .env..."
cp .env.example .env

# Configure production environment
sed -i "s|APP_NAME=Laravel|APP_NAME=Itinex|g" .env
sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
sed -i "s|APP_URL=http://localhost|APP_URL=http://itinex.twncolors.com|g" .env

# Database configuration
sed -i "s|DB_CONNECTION=sqlite|DB_CONNECTION=mysql|g" .env
sed -i "s|# DB_HOST=127.0.0.1|DB_HOST=127.0.0.1|g" .env
sed -i "s|# DB_PORT=3306|DB_PORT=3306|g" .env
sed -i "s|# DB_DATABASE=laravel|DB_DATABASE=twnceosl_itinex|g" .env
sed -i "s|# DB_USERNAME=root|DB_USERNAME=twnceosl_itinex|g" .env
sed -i "s|# DB_PASSWORD=|DB_PASSWORD=${DB_PASSWORD}|g" .env

echo "==> Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "==> Generating application key..."
php artisan key:generate --force

echo "==> Setting up document root symlink..."
# Remove the default subdomain folder and symlink to Laravel's public/
if [ -L ~/itinex.twncolors.com ]; then
    echo "    Symlink already exists."
elif [ -d ~/itinex.twncolors.com ]; then
    # Backup existing folder
    mv ~/itinex.twncolors.com ~/itinex.twncolors.com.bak
    echo "    Backed up existing folder to ~/itinex.twncolors.com.bak"
    ln -s ~/itinex/public ~/itinex.twncolors.com
    echo "    Created symlink: ~/itinex.twncolors.com -> ~/itinex/public"
else
    ln -s ~/itinex/public ~/itinex.twncolors.com
    echo "    Created symlink: ~/itinex.twncolors.com -> ~/itinex/public"
fi

echo "==> Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Running seeders..."
php artisan db:seed --force

echo "==> Creating storage symlink..."
php artisan storage:link 2>/dev/null || true

echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "============================================"
echo "  Itinex deployed successfully!"
echo "  Visit: http://itinex.twncolors.com"
echo "============================================"
