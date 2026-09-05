# VolNet Platform Starter (Windows PowerShell)
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptDir

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "            VolNet Platform Starter (Windows)             " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan

# Check if php is available
$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "[ERROR] PHP was not found in your System PATH." -ForegroundColor Red
    Write-Host "Please install PHP and ensure it is added to your Environment PATH variables." -ForegroundColor Yellow
    Read-Host "Press Enter to exit..."
    exit 1
}

# Ensure .env exists
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Write-Host "Creating .env from .env.example..." -ForegroundColor Yellow
        Copy-Item ".env.example" ".env"
    }
}

Write-Host "  🚀 Database: Supabase (PostgreSQL)" -ForegroundColor Cyan
Write-Host "  🌐 VolNet Server is LIVE at: http://localhost:8000" -ForegroundColor Green
Write-Host "  ⚡ Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan

# Open default browser
Start-Process "http://localhost:8000"

# Run PHP server
& php -d extension=pdo_pgsql -d extension=pgsql -d extension=mysqli -d extension=gd -d opcache.enable=0 -S 0.0.0.0:8000
