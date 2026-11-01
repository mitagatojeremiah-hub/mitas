param(
    [switch]$All
)

# check-php.ps1
# Locate a php.exe and run syntax checks (php -l) on project PHP files.
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File .\check-php.ps1 [--All]

$possible = @()
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if ($phpCmd) { $possible += $phpCmd.Source }
$possible += 'C:\xampp\php\php.exe','C:\Program Files\PHP\php.exe','C:\Program Files (x86)\PHP\php.exe'

$php = $null
foreach ($p in $possible | Select-Object -Unique) {
    if ([string]::IsNullOrWhiteSpace($p)) { continue }
    if (Test-Path $p) { $php = $p; break }
}

if (-not $php) {
    Write-Host "PHP executable not found in PATH or common locations." -ForegroundColor Yellow
    Write-Host "If you use XAMPP, try running this manually:" -ForegroundColor Cyan
    Write-Host "  C:\\xampp\\php\\php.exe -l index.php" -ForegroundColor Cyan
    Write-Host "Or add PHP to your PATH so 'php' is available from terminal." -ForegroundColor Cyan
    exit 1
}

Write-Host "Using PHP executable: $php" -ForegroundColor Green

if ($All) {
    Write-Host "Collecting all PHP files..." -ForegroundColor DarkCyan
    $files = Get-ChildItem -Path . -Recurse -Include *.php -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch '\\node_modules\\' -and $_.FullName -notmatch '\\.git\\' } | ForEach-Object { $_.FullName }
} else {
    $files = @('index.php','dashboard_api.php')
}

$allOk = $true
foreach ($f in $files) {
    if (-not (Test-Path $f)) { Write-Host "File not found: $f" -ForegroundColor Yellow; continue }
    Write-Host "`nLinting $f..." -ForegroundColor DarkCyan
    & $php -l $f
    if ($LASTEXITCODE -ne 0) { $allOk = $false }
}

if ($allOk) {
    Write-Host "`nAll files passed PHP lint (no syntax errors)." -ForegroundColor Green
    exit 0
} else {
    Write-Host "`nOne or more files have syntax errors. Fix them and re-run the script." -ForegroundColor Red
    exit 2
}
