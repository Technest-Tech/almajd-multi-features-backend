#!/bin/bash
# Run on the server (e.g. ssh root@138.68.46.231) to diagnose login issues
# Usage: bash check-server-login.sh   (from /var/www/almajd-backend or pass path as first arg)

set -e
APP_DIR="${1:-/var/www/almajd-backend}"
cd "$APP_DIR" || exit 1

echo "=========================================="
echo "Server / Login diagnostic - $(date)"
echo "=========================================="
echo ""

echo "--- 1. Services ---"
for s in nginx php8.2-fpm mysql; do
  status=$(systemctl is-active "$s" 2>/dev/null || echo "unknown")
  echo "  $s: $status"
done
echo ""

echo "--- 2. Login API (localhost) ---"
resp=$(curl -s -w "\nHTTP_CODE:%{http_code}" --max-time 10 \
  -X POST http://127.0.0.1/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@almajd.com","password":"admin123"}' 2>/dev/null || echo "HTTP_CODE:000")
echo "$resp"
echo ""

echo "--- 3. Last 50 lines of Laravel log ---"
tail -50 storage/logs/laravel.log 2>/dev/null || echo "(no log or not readable)"
echo ""

echo "--- 4. PHP/Artisan check ---"
php -v 2>/dev/null || echo "php not found"
php artisan --version 2>/dev/null || echo "artisan failed"
echo ""

echo "--- 5. .env (APP_KEY, DB, SANCTUM) ---"
grep -E '^APP_KEY=|^DB_|^SANCTUM_STATEFUL' .env 2>/dev/null | sed 's/APP_KEY=.*/APP_KEY=***/' | sed 's/DB_PASSWORD=.*/DB_PASSWORD=***/' || echo "(no .env)"
echo ""

echo "--- 6. Disk / permissions ---"
df -h /var/www 2>/dev/null | tail -1
ls -la storage/logs 2>/dev/null | head -5
echo ""
echo "Done."
