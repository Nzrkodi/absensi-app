@echo off
REM Laravel Scheduler Runner for Absensi System
REM This file runs Laravel scheduler automatically

REM Change to project directory
cd /d "I:\Ngoding\absensi-app"

REM Run Laravel scheduler
php artisan schedule:run

REM Log the execution (optional)
echo %date% %time% - Laravel scheduler executed >> scheduler_log.txt