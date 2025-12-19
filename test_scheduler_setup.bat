@echo off
echo ========================================
echo TESTING SCHEDULER SETUP
echo ========================================
echo.

echo 1. Testing batch file execution...
call run_scheduler.bat
echo.

echo 2. Checking if Laravel scheduler works...
php artisan schedule:list
echo.

echo 3. Testing holiday detection...
php artisan tinker --execute="echo 'Today is holiday: ' . (App\Models\Holiday::isTodayHoliday() ? 'YES' : 'NO'); echo PHP_EOL;"
echo.

echo 4. Checking log file...
if exist scheduler_log.txt (
    echo Last 5 lines of scheduler log:
    powershell "Get-Content scheduler_log.txt | Select-Object -Last 5"
) else (
    echo No scheduler log file found yet.
)
echo.

echo ========================================
echo TEST COMPLETED
echo ========================================
echo.
echo If everything looks good, you can now setup Task Scheduler!
echo Follow the guide in SETUP_TASK_SCHEDULER.md
pause