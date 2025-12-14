# Deployment Checklist - Queue Worker Setup

Follow these steps when deploying to your server to ensure the queue worker runs automatically.

## Pre-Deployment

- [ ] Ensure all code changes are committed
- [ ] Test queue functionality locally (optional but recommended)

## Server Deployment Steps

### 1. Upload Files to Server

Upload all files including:
- `app/Jobs/SendAutoBillingWhatsAppJob.php` (NEW)
- `app/Http/Controllers/Api/AutoBillingController.php` (UPDATED)
- `supervisor/laravel-worker.conf` (NEW)
- `setup-queue-worker.sh` (NEW)

### 2. Configure Environment

Edit `.env` file on server and ensure:
```env
QUEUE_CONNECTION=database
```

If not present, add it.

### 3. Run Database Migrations

```bash
cd /path/to/laravel-backend
php artisan migrate
```

This ensures the `jobs` table exists.

### 4. Set Up Queue Worker (Choose One Method)

#### Method A: Automated Setup (Recommended)

```bash
cd /path/to/laravel-backend
chmod +x setup-queue-worker.sh
sudo ./setup-queue-worker.sh
```

#### Method B: Manual Setup

1. **Install Supervisor** (if not installed):
   ```bash
   # Ubuntu/Debian
   sudo apt-get update && sudo apt-get install supervisor
   
   # CentOS/RHEL
   sudo yum install supervisor
   ```

2. **Copy Supervisor config:**
   ```bash
   sudo cp supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
   ```

3. **Edit the config file** to update the project path:
   ```bash
   sudo nano /etc/supervisor/conf.d/laravel-worker.conf
   ```
   
   Replace `/var/www/html/laravel-backend` with your actual project path.

4. **Start Supervisor:**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-worker:*
   ```

### 5. Verify Installation

```bash
# Check Supervisor status
sudo supervisorctl status laravel-worker:*

# Should show:
# laravel-worker:laravel-worker_00   RUNNING   pid XXXX, uptime X:XX:XX
# laravel-worker:laravel-worker_01   RUNNING   pid XXXX, uptime X:XX:XX
```

### 6. Test the Feature

1. Open your Flutter app
2. Navigate to Auto Billings
3. Select a month/year
4. Click "إرسال جميع الفواتير" (Send All Bills)
5. The request should return immediately with status "queued"
6. Check send logs to see messages being processed

### 7. Monitor Logs

```bash
# Worker logs
tail -f storage/logs/worker.log

# Application logs
tail -f storage/logs/laravel.log

# Supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log
```

## Post-Deployment Verification

- [ ] Queue worker is running (`supervisorctl status`)
- [ ] Jobs table exists in database
- [ ] `.env` has `QUEUE_CONNECTION=database`
- [ ] Can send bills and see them queued
- [ ] Messages are being sent (check logs)
- [ ] Worker restarts automatically on server reboot

## Troubleshooting

### Worker Not Running

1. Check Supervisor status:
   ```bash
   sudo supervisorctl status
   ```

2. Check logs:
   ```bash
   sudo tail -f /var/log/supervisor/supervisord.log
   tail -f storage/logs/worker.log
   ```

3. Verify permissions:
   ```bash
   sudo chown -R www-data:www-data storage
   sudo chmod -R 775 storage
   ```

### Jobs Not Processing

1. Check queue connection:
   ```bash
   php artisan queue:work database --once
   ```

2. Check for failed jobs:
   ```bash
   php artisan queue:failed
   ```

3. Clear config cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Worker Keeps Crashing

1. Check PHP memory limit:
   ```bash
   php -i | grep memory_limit
   ```

2. Increase timeout in Supervisor config if needed

3. Check for syntax errors in Job class

## Important Notes

- The queue worker must be running for jobs to process
- If the server reboots, Supervisor will automatically restart the worker
- Monitor logs regularly to catch issues early
- Failed jobs can be retried: `php artisan queue:retry all`

## Quick Commands Reference

```bash
# Check worker status
sudo supervisorctl status laravel-worker:*

# Restart worker
sudo supervisorctl restart laravel-worker:*

# Stop worker
sudo supervisorctl stop laravel-worker:*

# View worker logs
tail -f storage/logs/worker.log

# Process jobs manually (for testing)
php artisan queue:work database --once

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## Support

If issues persist:
1. Review `QUEUE_SETUP.md` for detailed documentation
2. Check all logs mentioned above
3. Verify database connection
4. Ensure Supervisor is running: `sudo systemctl status supervisor`
