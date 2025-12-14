# Queue Worker Setup Guide

This guide explains how to set up the Laravel queue worker to automatically process background jobs (like sending WhatsApp messages for bills) on your server.

## Overview

The "Send All Bills" feature now uses Laravel queues to process WhatsApp messages in the background. This means:
- ✅ HTTP requests return immediately
- ✅ Messages are sent asynchronously
- ✅ No timeout issues
- ✅ Better error handling and retries

## Prerequisites

- Laravel application deployed on server
- PHP CLI installed
- Supervisor installed (for automatic startup)
- Database configured

## Quick Setup (Automated)

1. **Make the setup script executable:**
   ```bash
   chmod +x setup-queue-worker.sh
   ```

2. **Run the setup script (as root or with sudo):**
   ```bash
   sudo ./setup-queue-worker.sh
   ```

   The script will:
   - Install Supervisor if not present
   - Create Supervisor configuration
   - Start the queue worker
   - Configure it to auto-start on server reboot

## Manual Setup

### Step 1: Configure Queue Connection

Ensure your `.env` file has:
```env
QUEUE_CONNECTION=database
```

### Step 2: Run Migrations

Make sure the jobs table exists:
```bash
php artisan migrate
```

### Step 3: Install Supervisor

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install supervisor
```

**CentOS/RHEL:**
```bash
sudo yum install supervisor
```

### Step 4: Create Supervisor Configuration

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/laravel-backend/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/laravel-backend/storage/logs/worker.log
stopwaitsecs=3600
```

**Important:** Replace `/path/to/your/laravel-backend` with your actual project path.

### Step 5: Start Supervisor

```bash
# Reload Supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start the worker
sudo supervisorctl start laravel-worker:*
```

## Verify It's Working

1. **Check Supervisor status:**
   ```bash
   sudo supervisorctl status laravel-worker:*
   ```

   You should see:
   ```
   laravel-worker:laravel-worker_00   RUNNING   pid 12345, uptime 0:05:23
   laravel-worker:laravel-worker_01   RUNNING   pid 12346, uptime 0:05:23
   ```

2. **Test by sending bills:**
   - Use the "Send All Bills" button in the app
   - Check the send logs to see messages being processed
   - View worker logs: `tail -f storage/logs/worker.log`

## Troubleshooting

### Worker Not Starting

1. **Check Supervisor logs:**
   ```bash
   sudo tail -f /var/log/supervisor/supervisord.log
   ```

2. **Check worker logs:**
   ```bash
   tail -f storage/logs/worker.log
   ```

3. **Verify permissions:**
   ```bash
   # Ensure storage directory is writable
   chmod -R 775 storage
   chown -R www-data:www-data storage
   ```

### Jobs Not Processing

1. **Check queue connection:**
   ```bash
   php artisan queue:work database --once
   ```

2. **Check for failed jobs:**
   ```bash
   php artisan queue:failed
   ```

3. **Retry failed jobs:**
   ```bash
   php artisan queue:retry all
   ```

### Worker Keeps Restarting

1. **Check for errors in logs:**
   ```bash
   tail -f storage/logs/worker.log
   ```

2. **Check PHP memory limit:**
   ```bash
   php -i | grep memory_limit
   ```

3. **Increase timeout if needed** (edit Supervisor config):
   ```ini
   command=php /path/to/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=120
   ```

## Useful Commands

```bash
# Check worker status
sudo supervisorctl status laravel-worker:*

# Restart worker
sudo supervisorctl restart laravel-worker:*

# Stop worker
sudo supervisorctl stop laravel-worker:*

# View logs
tail -f storage/logs/worker.log

# Check queue jobs
php artisan queue:work database --once

# Clear failed jobs
php artisan queue:flush

# Retry all failed jobs
php artisan queue:retry all
```

## Configuration Options

### Number of Workers

Edit `numprocs` in Supervisor config to run more workers:
```ini
numprocs=4  # Runs 4 worker processes
```

### Job Retries

Jobs are configured to retry 3 times. To change, edit the Job class:
```php
public $tries = 5; // Retry 5 times
```

### Delay Between Jobs

The controller dispatches jobs with 2-second delays. To change:
```php
$delay += 3; // 3 seconds between jobs
```

## Security Notes

- Ensure the worker runs as a non-root user (usually `www-data`)
- Keep Supervisor configuration files secure (chmod 600)
- Regularly monitor worker logs for errors
- Set up log rotation for worker logs

## After Deployment

After uploading to server:

1. **Run migrations:**
   ```bash
   php artisan migrate
   ```

2. **Clear config cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Restart workers:**
   ```bash
   sudo supervisorctl restart laravel-worker:*
   ```

## Support

If you encounter issues:
1. Check the logs in `storage/logs/worker.log`
2. Check Supervisor logs: `/var/log/supervisor/supervisord.log`
3. Verify `.env` has `QUEUE_CONNECTION=database`
4. Ensure database migrations are up to date
