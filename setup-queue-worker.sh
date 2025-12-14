#!/bin/bash

# Laravel Queue Worker Setup Script
# This script sets up the queue worker to run automatically on server startup

set -e

echo "=========================================="
echo "Laravel Queue Worker Setup"
echo "=========================================="

# Get the absolute path of the Laravel project
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_PATH="$SCRIPT_DIR"

# Detect web server user (common options)
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
else
    WEB_USER=$(whoami)
    echo "Warning: Could not detect web server user. Using current user: $WEB_USER"
fi

echo "Project path: $PROJECT_PATH"
echo "Web user: $WEB_USER"
echo ""

# Check if Supervisor is installed
if ! command -v supervisorctl &> /dev/null; then
    echo "Supervisor is not installed. Installing..."
    
    if [[ "$EUID" -ne 0 ]]; then
        echo "Please run as root to install Supervisor"
        exit 1
    fi
    
    if command -v apt-get &> /dev/null; then
        apt-get update
        apt-get install -y supervisor
    elif command -v yum &> /dev/null; then
        yum install -y supervisor
    else
        echo "Please install Supervisor manually"
        exit 1
    fi
fi

# Create Supervisor configuration
SUPERVISOR_CONF="/etc/supervisor/conf.d/laravel-worker.conf"
SUPERVISOR_CONF_TEMPLATE="$PROJECT_PATH/supervisor/laravel-worker.conf"

if [ -f "$SUPERVISOR_CONF_TEMPLATE" ]; then
    echo "Creating Supervisor configuration..."
    
    # Copy and customize the configuration
    cp "$SUPERVISOR_CONF_TEMPLATE" "$SUPERVISOR_CONF"
    
    # Replace placeholders
    sed -i "s|/var/www/html/laravel-backend|$PROJECT_PATH|g" "$SUPERVISOR_CONF"
    sed -i "s|user=www-data|user=$WEB_USER|g" "$SUPERVISOR_CONF"
    
    echo "Supervisor configuration created at: $SUPERVISOR_CONF"
else
    echo "Creating Supervisor configuration from scratch..."
    
    cat > "$SUPERVISOR_CONF" <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$WEB_USER
numprocs=2
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/worker.log
stopwaitsecs=3600
EOF
    
    echo "Supervisor configuration created at: $SUPERVISOR_CONF"
fi

# Ensure log directory exists
mkdir -p "$PROJECT_PATH/storage/logs"
chown -R "$WEB_USER:$WEB_USER" "$PROJECT_PATH/storage"

# Reload Supervisor configuration
echo ""
echo "Reloading Supervisor configuration..."
supervisorctl reread
supervisorctl update

# Start the worker
echo "Starting Laravel queue worker..."
supervisorctl start laravel-worker:*

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Queue worker is now running in the background."
echo ""
echo "Useful commands:"
echo "  - Check status: supervisorctl status laravel-worker:*"
echo "  - View logs: tail -f $PROJECT_PATH/storage/logs/worker.log"
echo "  - Restart worker: supervisorctl restart laravel-worker:*"
echo "  - Stop worker: supervisorctl stop laravel-worker:*"
echo ""
