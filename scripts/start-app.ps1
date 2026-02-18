param(
    [string]$PhpExe = "$env:PHP_EXE",
    [string]$Host = "127.0.0.1",
    [int]$Port = 8000
)

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if ([string]::IsNullOrWhiteSpace($PhpExe)) {
    $phpInPath = Get-Command php -ErrorAction SilentlyContinue
    if ($phpInPath) {
        $PhpExe = $phpInPath.Source
    }
}

if ([string]::IsNullOrWhiteSpace($PhpExe) -or !(Test-Path $PhpExe)) {
    Write-Error "PHP executable not found. Set -PhpExe or define PHP_EXE environment variable."
    exit 1
}

& $PhpExe artisan serve --host=$Host --port=$Port

