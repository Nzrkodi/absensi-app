# Laravel Scheduler Runner with Logging
# This PowerShell script runs Laravel scheduler and logs the results

# Set project directory
$projectPath = "I:\Ngoding\absensi-app"
$logFile = "$projectPath\scheduler_log.txt"

# Change to project directory
Set-Location $projectPath

# Get current timestamp
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

try {
    # Run Laravel scheduler
    $output = & php artisan schedule:run 2>&1
    
    # Log success
    Add-Content -Path $logFile -Value "[$timestamp] SUCCESS: Laravel scheduler executed"
    Add-Content -Path $logFile -Value "[$timestamp] Output: $output"
    
    Write-Host "[$timestamp] Laravel scheduler executed successfully"
    
} catch {
    # Log error
    Add-Content -Path $logFile -Value "[$timestamp] ERROR: $($_.Exception.Message)"
    Write-Host "[$timestamp] Error running scheduler: $($_.Exception.Message)"
}

# Add separator
Add-Content -Path $logFile -Value "----------------------------------------"