#!/bin/bash

echo "MySQL Database Setup for Laravel Backend"
echo "=========================================="
echo ""

# Read current .env values
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2)
DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)

echo "Current MySQL Configuration:"
echo "  Host: $DB_HOST"
echo "  Port: $DB_PORT"
echo "  Database: $DB_DATABASE"
echo "  Username: $DB_USERNAME"
echo "  Password: [hidden]"
echo ""

# Test connection
echo "Testing MySQL connection..."
if mysql -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -h "$DB_HOST" -P "$DB_PORT" -e "SELECT 1" 2>/dev/null; then
    echo "✓ MySQL connection successful!"
    echo ""
    
    # Create database
    echo "Creating database '$DB_DATABASE' if it doesn't exist..."
    mysql -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -h "$DB_HOST" -P "$DB_PORT" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✓ Database created/verified successfully!"
        echo ""
        echo "Running migrations..."
        php artisan migrate --force
    else
        echo "✗ Failed to create database. Please create it manually."
    fi
else
    echo "✗ MySQL connection failed!"
    echo ""
    echo "Please update your .env file with correct MySQL credentials:"
    echo "  DB_CONNECTION=mysql"
    echo "  DB_HOST=127.0.0.1"
    echo "  DB_PORT=3306"
    echo "  DB_DATABASE=almajd_certificates"
    echo "  DB_USERNAME=your_username"
    echo "  DB_PASSWORD=your_password"
    echo ""
    echo "Then run: php artisan migrate"
fi

