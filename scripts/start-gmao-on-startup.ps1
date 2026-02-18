$ErrorActionPreference = 'Stop'

$projectPath = 'C:\Users\issam\Desktop\GMAO-TECH'
$host = '127.0.0.1'
$appPort = 8000
$dbPort = 3306
$mysqlExe = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe'
$mysqlConfig = '--defaults-file=C:/MySQL/my.ini'

Set-Location $projectPath

# Ensure local MySQL instance is running for the Laravel app.
$dbListening = Get-NetTCPConnection -LocalPort $dbPort -State Listen -ErrorAction SilentlyContinue
if (-not $dbListening -and (Test-Path $mysqlExe)) {
    Start-Process -FilePath $mysqlExe -ArgumentList $mysqlConfig -WindowStyle Hidden
    Start-Sleep -Seconds 5
}

# Avoid creating duplicate Laravel servers when the user signs in multiple times.
$appListening = Get-NetTCPConnection -LocalAddress $host -LocalPort $appPort -State Listen -ErrorAction SilentlyContinue
if ($appListening) {
    exit 0
}

$phpCommand = "php artisan serve --host=$host --port=$appPort"
Start-Process -FilePath 'cmd.exe' -ArgumentList '/c', $phpCommand -WorkingDirectory $projectPath -WindowStyle Hidden
