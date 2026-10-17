param(
    [string]$AppUrl = "http://127.0.0.1:8000"
)

# 1. Determine Desktop Path (supports OneDrive redirected desktop and standard desktop)
$desktop = [Environment]::GetFolderPath('Desktop')
if (-not (Test-Path $desktop)) {
    if (Test-Path "$env:USERPROFILE\OneDrive\Desktop") {
        $desktop = "$env:USERPROFILE\OneDrive\Desktop"
    } elseif (Test-Path "$env:USERPROFILE\Desktop") {
        $desktop = "$env:USERPROFILE\Desktop"
    }
}

if (-not (Test-Path $desktop)) {
    Write-Error "Desktop path not found"
    exit 1
}

$oldShortcutPath = Join-Path $desktop "CSHOS DATACENTER.lnk"
if (Test-Path $oldShortcutPath) { Remove-Item $oldShortcutPath -Force -ErrorAction SilentlyContinue }
$shortcutPath = Join-Path $desktop "CSHOS StaffInfo.lnk"

# 2. Locate Browser executable
$chromeExe = "C:\Program Files\Google\Chrome\Application\chrome.exe"
if (-not (Test-Path $chromeExe)) {
    $chromeExe = "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
}
$edgeExe = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
if (-not (Test-Path $edgeExe)) {
    $edgeExe = "C:\Program Files\Microsoft\Edge\Application\msedge.exe"
}

# 3. Locate Application Icon
$icoPath = "C:\xampp\htdocs\cshdigitalhealth\public\app-icon.ico"
if (-not (Test-Path $icoPath)) {
    $icoPath = "C:\xampp\htdocs\cshdigitalhealth\public\favicon.ico"
}

# 4. Create Windows Desktop Shortcut (.lnk)
$wsh = New-Object -ComObject WScript.Shell
$sc = $wsh.CreateShortcut($shortcutPath)

if (Test-Path $chromeExe) {
    $sc.TargetPath = $chromeExe
    $sc.Arguments = "--app=$AppUrl"
    $sc.IconLocation = "$icoPath,0"
} elseif (Test-Path $edgeExe) {
    $sc.TargetPath = $edgeExe
    $sc.Arguments = "--app=$AppUrl"
    $sc.IconLocation = "$icoPath,0"
} else {
    $sc.TargetPath = $AppUrl
    if (Test-Path $icoPath) {
        $sc.IconLocation = "$icoPath,0"
    }
}

$sc.Description = "CSHOS StaffInfo"
$sc.WindowStyle = 1
$sc.Save()

# 5. Create Windows Startup Shortcut (.lnk) for auto-start on boot
try {
    $startupDir = [Environment]::GetFolderPath('Startup')
    if (-not $startupDir -or -not (Test-Path $startupDir)) {
        $startupDir = [Environment]::GetFolderPath('CommonStartup')
    }
    if ($startupDir -and (Test-Path $startupDir)) {
        $oldStartupScPath = Join-Path $startupDir "CSHOS DATACENTER.lnk"
        if (Test-Path $oldStartupScPath) { Remove-Item $oldStartupScPath -Force -ErrorAction SilentlyContinue }
        $startupScPath = Join-Path $startupDir "CSHOS StaffInfo.lnk"
        $scStart = $wsh.CreateShortcut($startupScPath)
        $scStart.TargetPath = $sc.TargetPath
        $scStart.Arguments = $sc.Arguments
        $scStart.IconLocation = $sc.IconLocation
        $scStart.Description = "CSHOS StaffInfo - AutoStart on Boot"
        $scStart.WindowStyle = 1
        $scStart.Save()
    }
} catch {}

Write-Output "SUCCESS: Created $shortcutPath"
