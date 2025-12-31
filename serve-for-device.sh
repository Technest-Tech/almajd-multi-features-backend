#!/bin/bash

# Script to start Laravel server for physical device testing
# This makes the server accessible from other devices on your network

echo "Starting Laravel server for physical device access..."
echo "Server will be accessible at: http://0.0.0.0:8000"
echo ""
echo "To connect from your physical device:"
echo "1. Make sure your phone and computer are on the same WiFi network"
echo "2. Find your computer's IP address:"
echo "   macOS/Linux: ifconfig | grep 'inet ' | grep -v 127.0.0.1"
echo "   Windows: ipconfig"
echo "3. Update Flutter AppConfig.backendBaseUrl with your IP (e.g., http://192.168.1.23:8000)"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php artisan serve --host=0.0.0.0 --port=8000










