#!/bin/bash

################################################################################
#  Legislative Services Committee Management System - Linux Setup Script
#  
#  This script helps you set up the application on Linux
#  Usage: chmod +x setup.sh && ./setup.sh
################################################################################

set -e

echo ""
echo "============================================================================"
echo "      Legislative Services Committee Management System"
echo "      Linux Setup & Installation Script"
echo "============================================================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "ERROR: This script must be run as root"
    echo "Usage: sudo ./setup.sh"
    exit 1
fi

# Get project path
PROJECT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WEB_ROOT="/var/www/html/legislative-cms"

echo "Project location: $PROJECT_PATH"
echo ""

# Install PHP and MySQL if not present
echo "Checking system requirements..."

if ! command -v php &> /dev/null; then
    echo "Installing PHP..."
    apt-get update
    apt-get install -y php php-cli php-mysql php-json php-curl php-mbstring
fi

if ! command -v mysql &> /dev/null; then
    echo "Installing MySQL..."
    apt-get install -y mysql-server
fi

if ! command -v apache2ctl &> /dev/null; then
    echo "Installing Apache..."
    apt-get install -y apache2 libapache2-mod-php
    a2enmod rewrite
    systemctl restart apache2
fi

echo "System requirements installed!"
echo ""

# Copy project to web root
echo "Setting up project directory..."
if [ ! -d "$WEB_ROOT" ]; then
    mkdir -p "$WEB_ROOT"
    cp -r "$PROJECT_PATH"/* "$WEB_ROOT"/
    echo "Project copied to $WEB_ROOT"
else
    echo "Directory already exists at $WEB_ROOT"
fi

# Set permissions
echo "Setting file permissions..."
chown -R www-data:www-data "$WEB_ROOT"
chmod -R 755 "$WEB_ROOT"
chmod -R 777 "$WEB_ROOT"/public/uploads
chmod -R 777 "$WEB_ROOT"/storage

echo "Permissions set!"
echo ""

# Start services
echo "Starting services..."
systemctl start apache2
systemctl start mysql

echo "Services started!"
echo ""

# Create database
echo "Creating database..."
read -p "Enter MySQL root password (or press Enter if none): " -s mysql_pass
echo ""

if [ -z "$mysql_pass" ]; then
    mysql -u root < "$PROJECT_PATH/database/schema.sql"
else
    mysql -u root -p"$mysql_pass" < "$PROJECT_PATH/database/schema.sql"
fi

echo "Database created successfully!"
echo ""

# Display instructions
echo "============================================================================"
echo "                      SETUP COMPLETE!"
echo "============================================================================"
echo ""
echo "Next steps:"
echo ""
echo "1. CONFIGURE DATABASE:"
echo "   - Edit: $WEB_ROOT/config/database.php"
echo "   - Update DB_USER and DB_PASS if needed"
echo ""
echo "2. OPEN APPLICATION:"
echo "   - Open browser to: http://localhost/legislative-cms/login.php"
echo "   - Or: http://your-domain.com/legislative-cms/login.php"
echo ""
echo "3. LOGIN WITH DEFAULT CREDENTIALS:"
echo "   - Username: admin"
echo "   - Password: admin123"
echo ""
echo "4. CHANGE ADMIN PASSWORD:"
echo "   - Go to: My Profile > Change Password"
echo "   - Set a new secure password"
echo ""
echo "============================================================================"
echo ""
echo "Application URL: http://localhost/legislative-cms/login.php"
echo ""

exit 0
