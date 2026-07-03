@echo off
REM ============================================================================
REM Legislative Services Committee Management System - Windows Setup Script
REM ============================================================================
REM This script helps you set up the application on Windows with XAMPP
REM Usage: Run this file as Administrator
REM ============================================================================

setlocal enabledelayedexpansion
cls

echo.
echo ============================================================================
echo       Legislative Services Committee Management System
echo       Windows Setup & Installation Script
echo ============================================================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo Please right-click this file and select "Run as Administrator"
    pause
    exit /b 1
)

REM Find XAMPP installation
if exist "C:\xampp\" (
    set XAMPP_PATH=C:\xampp
) else if exist "C:\Program Files\xampp\" (
    set XAMPP_PATH=C:\Program Files\xampp
) else (
    echo ERROR: XAMPP not found!
    echo Please install XAMPP from: https://www.apachefriends.org/
    pause
    exit /b 1
)

echo Found XAMPP at: %XAMPP_PATH%
echo.

REM Start MySQL and Apache
echo Starting MySQL and Apache services...
echo.

REM Start MySQL
echo Starting MySQL...
cd /d "%XAMPP_PATH%\mysql\bin"
mysqld --install xampp_mysql
net start xampp_mysql

REM Start Apache
echo Starting Apache...
cd /d "%XAMPP_PATH%\apache\bin"
httpd -k install
net start Apache2.4

echo.
echo Waiting for services to start...
timeout /t 3 /nobreak

REM Get project path
set PROJECT_PATH=%~dp0

echo.
echo Project location: %PROJECT_PATH%
echo.

REM Copy to XAMPP htdocs if needed
set HTDOCS=%XAMPP_PATH%\htdocs\legislative-cms

if not exist "%HTDOCS%" (
    echo Copying project to XAMPP htdocs...
    xcopy "%PROJECT_PATH%" "%HTDOCS%" /E /I /Y
    echo Project copied successfully!
) else (
    echo Project already exists at %HTDOCS%
)

echo.

REM Open browser
echo Opening phpMyAdmin to import database...
echo.
timeout /t 2 /nobreak

start "" "http://localhost/phpmyadmin"

REM Display instructions
echo.
echo ============================================================================
echo                       SETUP INSTRUCTIONS
echo ============================================================================
echo.
echo 1. IMPORT DATABASE (phpMyAdmin should now be open):
echo    - Create new database: legislative_cms
echo    - Select database
echo    - Go to "Import" tab
echo    - Choose file: %HTDOCS%\database\schema.sql
echo    - Click "Go"
echo.
echo 2. CONFIGURE DATABASE:
echo    - Edit: %HTDOCS%\config\database.php
echo    - Update DB_HOST, DB_USER, DB_PASS if needed
echo.
echo 3. OPEN APPLICATION:
echo    - Open browser to: http://localhost/legislative-cms/login.php
echo.
echo 4. LOGIN WITH DEFAULT CREDENTIALS:
echo    - Username: admin
echo    - Password: admin123
echo.
echo 5. IMPORTANT - CHANGE ADMIN PASSWORD:
echo    - Go to: My Profile > Change Password
echo    - Set a new secure password
echo.
echo ============================================================================
echo.

echo Done! Press any key to continue...
pause

echo.
echo Opening application...
timeout /t 2 /nobreak
start "" "http://localhost/legislative-cms/login.php"

exit /b 0
