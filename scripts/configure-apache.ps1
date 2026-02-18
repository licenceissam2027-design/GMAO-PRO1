param(
    [string]$ApacheRoot = "C:\tools\Apache24",
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot),
    [string]$PhpRoot = "C:\tools\php\8.3"
)

$httpdConf = Join-Path $ApacheRoot "conf\httpd.conf"
$vhostConf = Join-Path $ApacheRoot "conf\extra\gmao-vhost.conf"

if (!(Test-Path $httpdConf)) {
    Write-Error "httpd.conf not found at $httpdConf. Install Apache first or update -ApacheRoot."
    exit 1
}

if (!(Test-Path (Join-Path $PhpRoot "php8apache2_4.dll"))) {
    Write-Error "php8apache2_4.dll not found in $PhpRoot"
    exit 1
}

$projectPublic = (Join-Path $ProjectRoot "public").Replace('\\', '/')
$phpRootUnix = $PhpRoot.Replace('\\', '/')

# Backup config once
$backupPath = "$httpdConf.bak"
if (!(Test-Path $backupPath)) {
    Copy-Item $httpdConf $backupPath -Force
}

$content = Get-Content $httpdConf -Raw

# Core modules and PHP
if ($content -notmatch '(?m)^LoadModule rewrite_module modules/mod_rewrite\.so') {
    $content = $content -replace '(?m)^#\s*LoadModule rewrite_module modules/mod_rewrite\.so', 'LoadModule rewrite_module modules/mod_rewrite.so'
}

if ($content -notmatch '(?m)^LoadModule vhost_alias_module modules/mod_vhost_alias\.so') {
    $content = $content -replace '(?m)^#\s*LoadModule vhost_alias_module modules/mod_vhost_alias\.so', 'LoadModule vhost_alias_module modules/mod_vhost_alias.so'
}

if ($content -notmatch '(?m)^LoadModule php_module "[^"]+php8apache2_4\.dll"') {
    $phpModule = "LoadModule php_module `"$phpRootUnix/php8apache2_4.dll`""
    $content = $content + "`r`n$phpModule`r`nAddHandler application/x-httpd-php .php`r`nPHPIniDir `"$phpRootUnix`"`r`n"
}

# Main docroot for direct localhost usage
$content = $content -replace '(?m)^DocumentRoot\s+"[^"]+"', "DocumentRoot `"$projectPublic`""
$content = $content -replace '(?s)<Directory\s+"[^\"]+">\s*\r?\n\s*AllowOverride\s+None\s*\r?\n\s*Require\s+all\s+granted\s*\r?\n</Directory>', "<Directory `"$projectPublic`">`r`n    AllowOverride All`r`n    Require all granted`r`n</Directory>"

# Ensure vhosts include is active
if ($content -notmatch '(?m)^Include conf/extra/httpd-vhosts\.conf') {
    $content = $content -replace '(?m)^#\s*Include conf/extra/httpd-vhosts\.conf', 'Include conf/extra/httpd-vhosts.conf'
}

Set-Content $httpdConf $content

# Write dedicated vhost file
$vhostTemplate = @"
<VirtualHost *:80>
    ServerName gmao.local
    ServerAlias www.gmao.local
    DocumentRoot "$projectPublic"

    <Directory "$projectPublic">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/gmao-error.log"
    CustomLog "logs/gmao-access.log" combined
</VirtualHost>
"@
Set-Content $vhostConf $vhostTemplate

# Include dedicated vhost file from httpd-vhosts.conf
$httpdVhosts = Join-Path $ApacheRoot "conf\extra\httpd-vhosts.conf"
if (Test-Path $httpdVhosts) {
    $vhContent = Get-Content $httpdVhosts -Raw
    if ($vhContent -notmatch 'Include\s+conf/extra/gmao-vhost\.conf') {
        Add-Content $httpdVhosts "`r`nInclude conf/extra/gmao-vhost.conf`r`n"
    }
}

# hosts file update (requires admin)
$hostsPath = "C:\Windows\System32\drivers\etc\hosts"
try {
    $hostsContent = Get-Content $hostsPath -Raw
    if ($hostsContent -notmatch '(?m)^127\.0\.0\.1\s+gmao\.local') {
        Add-Content $hostsPath "`r`n127.0.0.1 gmao.local`r`n"
    }
    Write-Output "Hosts file updated: gmao.local"
}
catch {
    Write-Warning "Unable to update hosts file automatically. Run PowerShell as Administrator and add gmao.local manually."
}

Write-Output "Apache configured successfully."
Write-Output "Run: $ApacheRoot\\bin\\httpd.exe -t"
Write-Output "Run: $ApacheRoot\\bin\\httpd.exe"

