$envFile = Join-Path $PSScriptRoot "..\.env"
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*([^#][^=]+)=(.*)$') {
        $name = $matches[1].Trim()
        $value = $matches[2].Trim().Trim('"')
        Set-Item -Path "Env:$name" -Value $value
    }
}

$stamp = Get-Date -Format "yyyyMMdd-HHmm"
$directory = Join-Path $PSScriptRoot "..\storage\app\backups"
New-Item -ItemType Directory -Force -Path $directory | Out-Null
$out = Join-Path $directory "sats-$stamp.sql"
$mysql = "C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe"
if (-not (Test-Path $mysql)) { $mysql = "mysqldump" }

& $mysql --host=$env:DB_HOST --port=$env:DB_PORT --user=$env:DB_USERNAME "--password=$env:DB_PASSWORD" --single-transaction --routines --triggers $env:DB_DATABASE | Set-Content -Encoding utf8 $out
Write-Output "Wrote $out"
