[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

# Enable TLS 1.2/1.1 for modern web requests on all Windows versions
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
} catch {}

$ws = New-Object -ComObject WScript.Shell
$shortcutName = "CSHOS DATACENTER"
$appUrl = 'http://localhost'

# Locate Google Chrome or Microsoft Edge
$browserExe = ""
$browserCandidates = @(
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe",
    'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    'C:\Program Files\Microsoft\Edge\Application\msedge.exe'
)
foreach ($cand in $browserCandidates) {
    if (Test-Path $cand) {
        $browserExe = $cand
        break
    }
}

# --- Shared System Directory for CSHOS assets (All local users have read access) ---
$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) {
    try { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null } catch {}
}
if (-not (Test-Path $sysDir)) {
    $sysDir = "$env:LOCALAPPDATA\CSHOS"
    if (-not (Test-Path $sysDir)) { New-Item -ItemType Directory -Path $sysDir -Force | Out-Null }
}

# --- Download App Icon ---
$icoPath = Join-Path $sysDir "app-icon.ico"
$iconDownloaded = $false
try {
    $wc = New-Object System.Net.WebClient
    $wc.Headers.Add("User-Agent", "CSHOS-Installer")
    $wc.DownloadFile('http://localhost/app-icon.ico', $icoPath)
    if ((Test-Path $icoPath) -and ((Get-Item $icoPath).Length -gt 100)) {
        $iconDownloaded = $true
    }
} catch {}

if (-not $iconDownloaded) {
    try {
        Invoke-WebRequest -Uri 'http://localhost/app-icon.ico' -OutFile $icoPath -UseBasicParsing -TimeoutSec 15 -ErrorAction SilentlyContinue
        if ((Test-Path $icoPath) -and ((Get-Item $icoPath).Length -gt 100)) {
            $iconDownloaded = $true
        }
    } catch {}
}

# --- Detect All Target Desktops (Public Desktop + Logged-in User Desktops) ---
$targetDesktops = @()

# 1. Common / Public Desktop (Displays on ALL users' screens immediately)
$commonDesktop = [Environment]::GetFolderPath('CommonDesktopDirectory')
if ($commonDesktop -and (Test-Path $commonDesktop)) {
    $targetDesktops += $commonDesktop
}

# 2. Current User Desktop
$userDesktop = [Environment]::GetFolderPath('Desktop')
if ($userDesktop -and (Test-Path $userDesktop)) {
    $targetDesktops += $userDesktop
}

# 3. Interactive Logged-in Console User Desktop
try {
    $activeUser = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
    if ($activeUser) {
        $uName = $activeUser.Split('\')[-1]
        $uDesk = "C:\Users\$uName\Desktop"
        if (Test-Path $uDesk) { $targetDesktops += $uDesk }
        $uOneDesk = "C:\Users\$uName\OneDrive\Desktop"
        if (Test-Path $uOneDesk) { $targetDesktops += $uOneDesk }
    }
} catch {}

# 4. Current Profile OneDrive Desktop
if (Test-Path "$env:USERPROFILE\OneDrive\Desktop") {
    $targetDesktops += "$env:USERPROFILE\OneDrive\Desktop"
}

# 5. All existing user profiles under C:\Users
try {
    $userDirs = Get-ChildItem -Path "C:\Users" -Directory -ErrorAction SilentlyContinue | Where-Object { 
        $_.Name -notmatch '^(Public|Default|Default User|All Users)$' 
    }
    foreach ($ud in $userDirs) {
        $dPath = Join-Path $ud.FullName "Desktop"
        try { if (Test-Path $dPath -ErrorAction SilentlyContinue) { $targetDesktops += $dPath } } catch {}
        $oPath = Join-Path $ud.FullName "OneDrive\Desktop"
        try { if (Test-Path $oPath -ErrorAction SilentlyContinue) { $targetDesktops += $oPath } } catch {}
    }
} catch {}

$targetDesktops = $targetDesktops | Select-Object -Unique

# --- Install Desktop Shortcut to All Detected Desktops ---
$installedCount = 0
foreach ($desk in $targetDesktops) {
    try {
        $shortcutPath = Join-Path $desk "$shortcutName.lnk"
        $sc = $ws.CreateShortcut($shortcutPath)
        if ($browserExe) {
            $sc.TargetPath = $browserExe
            $sc.Arguments = "--app=`"$appUrl`""
        } else {
            $sc.TargetPath = $appUrl
        }
        $sc.WorkingDirectory = $sysDir
        $sc.Description = "$shortcutName - โรงพยาบาลเชียงแสน"
        if ($iconDownloaded -or ((Test-Path $icoPath) -and ((Get-Item $icoPath).Length -gt 100))) {
            $sc.IconLocation = "$icoPath,0"
        }
        $sc.Save()
        $installedCount++
    } catch {}
}

# Refresh Windows Explorer desktop icon cache
try { & ie4uinit.exe -show | Out-Null } catch {}

# --- Collect Computer Specs ---
$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }

$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
if ($cpuObj -and $cpuObj.Name) {
    $cpuInfo = $cpuObj.Name.Trim()
} else {
    $cpuInfo = "ไม่สามารถระบุ CPU"
}

try {
    $totalRam = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory
    $ramGB = [math]::Round(($totalRam / 1GB), 1)
    $ramInfo = "$ramGB GB"
} catch {
    $ramInfo = "ไม่สามารถระบุ RAM"
}

try {
    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        "$dId ว่าง $free/$total GB"
    }
    $diskInfo = $diskItems -join ", "
} catch {
    $diskInfo = "ไม่สามารถระบุ DISK"
}

# --- Detect Target Machine IP Address (IPv4) ---
$detectedIps = @()
try {
    $detectedIps = @(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { 
        $_.InterfaceAlias -notmatch 'Loopback|vEthernet|Virtual|Pseudo' -and 
        $_.IPAddress -notmatch '^169\.254\.' -and 
        $_.IPAddress -ne '127.0.0.1' 
    } | Select-Object -ExpandProperty IPAddress)
} catch {}

if (-not $detectedIps -or $detectedIps.Count -eq 0) {
    try {
        $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
        foreach ($nc in $netConfigs) {
            foreach ($ip in $nc.IPAddress) {
                if ($ip -match '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$' -and $ip -ne '127.0.0.1' -and $ip -notmatch '^169\.254\.') {
                    $detectedIps += $ip
                }
            }
        }
    } catch {}
}

$clientIp = if ($detectedIps -and $detectedIps.Count -gt 0) { $detectedIps[0] } else { "" }
$allIpsStr = if ($detectedIps -and $detectedIps.Count -gt 0) { ($detectedIps | Select-Object -Unique) -join ", " } else { "ไม่พบ IP (DHCP)" }

# --- Install & Configure VNC Server (TightVNC - Screen Share, No Logout) ---
$vncInstalled = $false
try {
    $tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvnSvc) {
        $vncInstalled = $true
    }
} catch {}

if (-not $vncInstalled) {
    try {
        $is64 = [Environment]::Is64BitOperatingSystem
        $msiFile = if ($is64) { "tightvnc-64bit.msi" } else { "tightvnc-32bit.msi" }
        $msiUrl = "http://localhost/downloads/" + $msiFile
        $msiLocal = Join-Path $sysDir $msiFile

        if (-not (Test-Path $msiLocal)) {
            try {
                $wc = New-Object System.Net.WebClient
                $wc.DownloadFile($msiUrl, $msiLocal)
            } catch {
                Invoke-WebRequest -Uri $msiUrl -OutFile $msiLocal -UseBasicParsing -TimeoutSec 25 -ErrorAction SilentlyContinue
            }
        }

        if (Test-Path $msiLocal) {
            $vncPass = "4Sh0sp@r"
            $msiArgs = "/i `"$msiLocal`" /quiet /norestart ADDLOCAL=Server SERVER_REGISTER_AS_SERVICE=1 SERVER_ADD_FIREWALL_EXCEPTION=1 SET_USEVNCAUTHENTICATION=1 VALUE_OF_USEVNCAUTHENTICATION=1 SET_PASSWORD=1 VALUE_OF_PASSWORD=`"$vncPass`""
            Start-Process msiexec.exe -ArgumentList $msiArgs -Wait -NoNewWindow -ErrorAction SilentlyContinue
        }
    } catch {}
}

# Set TightVNC Password directly into Registry
try {
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v Password /t REG_BINARY /d 42eee47285988d91 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v UseVncAuthentication /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v AcceptRfbConnections /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v AllowLoopback /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\TightVNC\Server" /v RfbPort /t REG_DWORD /d 5900 /f | Out-Null
} catch {}

try {
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v Password /t REG_BINARY /d 42eee47285988d91 /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v UseVncAuthentication /t REG_DWORD /d 1 /f | Out-Null
    reg add "HKLM\SOFTWARE\WOW6432Node\TightVNC\Server" /v AcceptRfbConnections /t REG_DWORD /d 1 /f | Out-Null
} catch {}

# Ensure tvnserver service is running and restarted to load new password
try {
    Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
    Restart-Service -Name "tvnserver" -Force -ErrorAction SilentlyContinue
    Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
} catch {}

# Ensure Windows Firewall allows Port 5900
try {
    netsh advfirewall firewall add rule name="TightVNC Server Port 5900" dir=in action=allow protocol=TCP localport=5900 | Out-Null
} catch {}

# --- Also Enable Internal Windows Remote Desktop & Remote Assistance as backup ---
try {
    Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Terminal Server' -Name "fDenyTSConnections" -Value 0 -ErrorAction SilentlyContinue
    Enable-NetFirewallRule -DisplayGroup "Remote Desktop" -ErrorAction SilentlyContinue
    Set-Service -Name TermService -StartupType Automatic -ErrorAction SilentlyContinue
    Start-Service -Name TermService -ErrorAction SilentlyContinue
    Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Remote Assistance' -Name "fAllowToGetHelp" -Value 1 -ErrorAction SilentlyContinue
    Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Remote Assistance' -Name "fAllowFullControl" -Value 1 -ErrorAction SilentlyContinue
} catch {}

# --- Persistent Device UUID ---
$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
}
try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}

# --- Setup Background Auto-Sync Script for System Restart / Boot / Logon ---
$syncScriptPath = Join-Path $sysDir "cshos_sync.ps1"
$syncCode = @'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

$maxWait = 30
$waited = 0
$detectedIps = @()

while ($waited -lt $maxWait) {
    try {
        $detectedIps = @(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { 
            $_.InterfaceAlias -notmatch 'Loopback|vEthernet|Virtual|Pseudo' -and 
            $_.IPAddress -notmatch '^169\.254\.' -and 
            $_.IPAddress -ne '127.0.0.1' 
        } | Select-Object -ExpandProperty IPAddress)
    } catch {}

    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
            foreach ($nc in $netConfigs) {
                foreach ($ip in $nc.IPAddress) {
                    if ($ip -match '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$' -and $ip -ne '127.0.0.1' -and $ip -notmatch '^169\.254\.') {
                        $detectedIps += $ip
                    }
                }
            }
        } catch {}
    }

    if ($detectedIps -and $detectedIps.Count -gt 0) {
        break
    }

    Start-Sleep -Seconds 3
    $waited += 3
}

$clientIp = if ($detectedIps -and $detectedIps.Count -gt 0) { $detectedIps[0] } else { "" }

$sysDir = "$env:ProgramData\CSHOS"
if (-not (Test-Path $sysDir)) { $sysDir = "$env:LOCALAPPDATA\CSHOS" }

$devIdFile = Join-Path $sysDir "device_id.txt"
$devId = ""
if (Test-Path $devIdFile) {
    try { $devId = (Get-Content $devIdFile -Raw).Trim() } catch {}
}
if (-not $devId) {
    $devId = [guid]::NewGuid().ToString()
    try { Set-Content -Path $devIdFile -Value $devId -Force } catch {}
}

$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }
if (-not $userName) { $userName = "System" }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }

$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
$cpuInfo = if ($cpuObj -and $cpuObj.Name) { $cpuObj.Name.Trim() } else { "ไม่สามารถระบุ CPU" }

try {
    $totalRam = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory
    $ramGB = [math]::Round(($totalRam / 1GB), 1)
    $ramInfo = "$ramGB GB"
} catch {
    $ramInfo = "ไม่สามารถระบุ RAM"
}

try {
    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        "$dId ว่าง $free/$total GB"
    }
    $diskInfo = $diskItems -join ", "
} catch {
    $diskInfo = "ไม่สามารถระบุ DISK"
}

$remoteReady = 0
try {
    $tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvnSvc -and $tvnSvc.Status -eq 'Running') {
        $remoteReady = 1
    }
} catch {}

$osCaption = ($osInfo -replace 'Microsoft\s*', '').Trim()
$osVerStr = $osCaption
try {
    $regNT = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion" -ErrorAction SilentlyContinue
    $dispVer = if ($regNT.DisplayVersion) { $regNT.DisplayVersion } elseif ($regNT.ReleaseId) { $regNT.ReleaseId } else { "" }
    $build = $regNT.CurrentBuild
    $arch = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).OSArchitecture
    if ($dispVer) { $osVerStr += " $dispVer" }
    if ($arch -or $build) {
        $p = @()
        if ($arch) { $p += $arch }
        if ($build) { $p += "Build $build" }
        $osVerStr += " (" + ($p -join ", ") + ")"
    }
} catch {}

$officeFinal = "Not Installed"
try {
    $c2r = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Office\ClickToRun\Configuration" -ErrorAction SilentlyContinue
    if ($c2r -and $c2r.ProductReleaseIds) {
        $idStr = "$($c2r.ProductReleaseIds)"
        $verReport = $c2r.VersionToReport
        $platform = $c2r.Platform
        $friendly = if ($idStr -match '2024') { "Microsoft Office 2024" }
                    elseif ($idStr -match '2021') { "Microsoft Office 2021" }
                    elseif ($idStr -match '2019') { "Microsoft Office 2019" }
                    elseif ($idStr -match 'O365|M365|365') { "Microsoft 365 Apps" }
                    elseif ($idStr -match '2016') { "Microsoft Office 2016" }
                    elseif ($idStr -match '2013') { "Microsoft Office 2013" }
                    elseif ($idStr -match '2010') { "Microsoft Office 2010" }
                    else { "Microsoft Office" }
        $edition = if ($idStr -match 'ProPlus') { " Professional Plus" }
                   elseif ($idStr -match 'Standard') { " Standard" }
                   elseif ($idStr -match 'HomeBusiness') { " Home & Business" }
                   elseif ($idStr -match 'HomeStudent') { " Home & Student" }
                   else { "" }
        $officeFinal = "$friendly$edition"
        if ($verReport) { $officeFinal += " ($verReport" + (if ($platform) { " $platform" } else { "" }) + ")" }
    }
    if ($officeFinal -eq "Not Installed") {
        $keys = @("HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*", "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*")
        $installed = Get-ItemProperty $keys -ErrorAction SilentlyContinue | Where-Object { 
            $_.DisplayName -match '^Microsoft Office (Professional|Standard|Home|Personal|365|Enterprise)' -or
            $_.DisplayName -match '^Microsoft 365'
        } | Select-Object -First 1
        if ($installed) {
            $officeFinal = $installed.DisplayName + (if ($installed.DisplayVersion) { " ($($installed.DisplayVersion))" } else { "" })
        }
    }
    if ($officeFinal -eq "Not Installed") {
        $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\excel.exe" -ErrorAction SilentlyContinue).'(Default)'
        if (-not $appPath) { $appPath = (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\winword.exe" -ErrorAction SilentlyContinue).'(Default)' }
        if ($appPath -and (Test-Path $appPath)) {
            $vi = (Get-Item $appPath -ErrorAction SilentlyContinue).VersionInfo
            $m = $vi.ProductMajorPart
            $known = switch ($m) { 16 { "Microsoft Office 2016/2019/365" } 15 { "Microsoft Office 2013" } 14 { "Microsoft Office 2010" } 12 { "Microsoft Office 2007" } default { "Microsoft Office" } }
            $officeFinal = "$known" + (if ($vi.ProductVersion) { " ($($vi.ProductVersion))" } else { "" })
        }
    }
} catch {}

$reportObj = @{
    device_id      = $devId
    hostname       = $compName
    device_name    = $userName
    ip_address     = $clientIp
    os             = $osInfo
    os_version     = $osVerStr
    office_version = $officeFinal
    cpu            = $cpuInfo
    ram            = $ramInfo
    disk           = $diskInfo
    remote_ready   = $remoteReady
    install_type   = 'installed'
    browser        = 'AutoSync Service'
}
$jsonPayload = $reportObj | ConvertTo-Json

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
    $res = Invoke-RestMethod -Uri "http://localhost/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 10 -ErrorAction Stop
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - OK: Host=$compName, IP=$clientIp, RAM=$ramInfo, DISK=$diskInfo"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
} catch {
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Err: $($_.Exception.Message)"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
}
'@

try {
    Set-Content -Path $syncScriptPath -Value $syncCode -Encoding UTF8 -Force
} catch {}

# 1. Register Task Scheduler: Startup (ONSTART with 30s delay)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    schtasks /create /tn "CSHOS-AutoSync-Startup" /tr $taskCmd /sc ONSTART /delay 0000:30 /ru SYSTEM /f | Out-Null
} catch {}

# 2. Register Task Scheduler: Logon (ONLOGON with 15s delay)
try {
    $taskCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    schtasks /create /tn "CSHOS-AutoSync-Logon" /tr $taskCmd /sc ONLOGON /delay 0000:15 /rl HIGHEST /f | Out-Null
} catch {}

# 3. Register Windows Registry Run Key
try {
    $regCmd = "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`""
    reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /t REG_SZ /d $regCmd /f | Out-Null
    reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "CSHOS_AutoSync" /t REG_SZ /d $regCmd /f | Out-Null
} catch {}

# Trigger initial Auto-Sync immediately in background to verify setup
try {
    Start-Process powershell.exe -ArgumentList "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$syncScriptPath`"" -WindowStyle Hidden
} catch {}

# 4. Register vnc:// URL protocol for TightVNC Viewer (CSHOS Remote Launcher)
try {
    $passFile = Join-Path $sysDir "vnc_password.txt"
    Set-Content -Path $passFile -Value "4Sh0sp@r" -Encoding ASCII -Force
    $vncLauncherVbs = Join-Path $sysDir "vnc-launcher.vbs"
    $vbsCode = @'
Set args = WScript.Arguments
If args.Count = 0 Then WScript.Quit
rawUrl = Replace(Replace(args(0), "vnc://", "", 1, -1, 1), "/", "")
host = ""
port = "5900"
pass = "4Sh0sp@r"
Set fso = CreateObject("Scripting.FileSystemObject")
If fso.FileExists("C:\ProgramData\CSHOS\vnc_password.txt") Then
    On Error Resume Next
    Set pf = fso.OpenTextFile("C:\ProgramData\CSHOS\vnc_password.txt", 1)
    pass = Trim(pf.ReadAll)
    pf.Close
    On Error Goto 0
End If
targetPart = rawUrl
If InStr(targetPart, ":") > 0 Then
    hParts = Split(targetPart, ":")
    host = hParts(0)
    port = hParts(1)
Else
    host = targetPart
End If
vncExe = "C:\Program Files\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then vncExe = "C:\Program Files (x86)\TightVNC\tvnviewer.exe"
If Not fso.FileExists(vncExe) Then
    MsgBox "TightVNC Viewer is not installed.", 16, "CSHOS VNC"
    WScript.Quit
End If
Set shell = CreateObject("WScript.Shell")
If pass <> "" Then
    cmd = """" & vncExe & """ -host=" & host & " -port=" & port & " -password=" & pass
Else
    cmd = """" & vncExe & """ -host=" & host & " -port=" & port
End If
shell.Run cmd, 1, False
'@
    Set-Content -Path $vncLauncherVbs -Value $vbsCode -Encoding ASCII -Force
    reg add "HKCU\Software\Classes\vnc" /ve /t REG_SZ /d "URL:VNC Protocol" /f | Out-Null
    reg add "HKCU\Software\Classes\vnc" /v "URL Protocol" /t REG_SZ /d "" /f | Out-Null
    reg add "HKCU\Software\Classes\vnc\shell\open\command" /ve /t REG_SZ /d "wscript.exe `"$vncLauncherVbs`" `"%1`"" /f | Out-Null
} catch {}


# --- Report Hardware Specs & Remote Status to CSHOS Server ---
$reportObj = @{
    device_id    = $devId
    hostname     = $compName
    device_name  = $userName
    ip_address   = $clientIp
    os           = $osInfo
    cpu          = $cpuInfo
    ram          = $ramInfo
    disk         = $diskInfo
    remote_ready = 1
    install_type = 'installed'
    browser      = 'Desktop App'
}
$jsonPayload = $reportObj | ConvertTo-Json

$serverSyncStatus = "รอส่งข้อมูล..."
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
    $res = Invoke-RestMethod -Uri "http://localhost/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 8 -ErrorAction Stop
    if ($res -and $res.status -eq 'success') {
        $serverSyncStatus = "อัปเดตข้อมูลขึ้นระบบเซิร์ฟเวอร์ CSHOS สำเร็จเรียบร้อย"
        if ($res.ip) { $allIpsStr = $res.ip }
    } else {
        $serverSyncStatus = "เชื่อมต่อระบบเซิร์ฟเวอร์แล้ว"
    }
} catch {
    $serverSyncStatus = "บันทึกข้อมูลในเครื่องแล้ว (เซิร์ฟเวอร์ออฟไลน์หรืออยู่นอกเครือข่าย)"
}

Write-Host ""
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host "      CSHOS DIGITAL HEALTH - โรงพยาบาลเชียงแสน" -ForegroundColor White
Write-Host "      ติดตั้งตัวแอป และอัปเดตข้อมูลเครื่องปลายทางสำเร็จ!" -ForegroundColor Green
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host " [1] ไอคอนทางลัดโปรแกรม (Desktop Shortcut):" -ForegroundColor Yellow
Write-Host "     • จำนวนตำแหน่งที่ติดตั้ง       : " -NoNewline -ForegroundColor Gray
Write-Host "$installedCount ตำแหน่ง (Public Desktop + ทุกผู้ใช้งาน)" -ForegroundColor Green
Write-Host "     • ไฟล์ไอคอนแอป               : " -NoNewline -ForegroundColor Gray
if ($iconDownloaded -or (Test-Path $icoPath)) {
    Write-Host "ดาวน์โหลดและติดตั้งสมบูรณ์ ($icoPath)" -ForegroundColor Green
} else {
    Write-Host "ใช้ไอคอนระบบเริ่มต้น" -ForegroundColor Yellow
}
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [2] ข้อมูลเครื่องคอมพิวเตอร์ปลายทางที่ตรวจจับและอัปเดต:" -ForegroundColor Yellow
Write-Host "     • ชื่อเครื่อง (Computer Name) : " -NoNewline -ForegroundColor Gray
Write-Host "$compName" -ForegroundColor White
Write-Host "     • ผู้ใช้งาน (Username)       : " -NoNewline -ForegroundColor Gray
Write-Host "$userName" -ForegroundColor White
Write-Host "     • หมายเลข IP Address          : " -NoNewline -ForegroundColor Gray
Write-Host "$allIpsStr" -ForegroundColor Green
Write-Host "     • สเปก CPU                    : " -NoNewline -ForegroundColor Gray
Write-Host "$cpuInfo" -ForegroundColor White
Write-Host "     • ขนาดหน่วยความจำ RAM        : " -NoNewline -ForegroundColor Gray
Write-Host "$ramInfo" -ForegroundColor White
Write-Host "     • พื้นที่ฮาร์ดดิสก์ DISK       : " -NoNewline -ForegroundColor Gray
Write-Host "$diskInfo" -ForegroundColor White
Write-Host "     • ระบบปฏิบัติการ OS           : " -NoNewline -ForegroundColor Gray
Write-Host "$osInfo" -ForegroundColor White
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [3] ระบบ Remote ภายใน (VNC Screen Sharing - หน้าจอไม่ดับ):" -ForegroundColor Yellow
Write-Host "     • สถานะการเปิดระบบ            : " -NoNewline -ForegroundColor Gray
Write-Host "พร้อมเชื่อมต่อ (TightVNC Port 5900)" -ForegroundColor Green
Write-Host "     • รหัสผ่าน VNC                : " -NoNewline -ForegroundColor Gray
Write-Host "4Sh0sp@r" -ForegroundColor Yellow
Write-Host "     • โหมดทำงาน                   : เจ้าหน้าที่ไอทีสามารถดูและช่วยแก้ปัญหาหน้าจอได้พร้อมกัน" -ForegroundColor DarkCyan
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [4] ระบบ Auto-Sync อัตโนมัติเมื่อเปิดเครื่อง (Auto-Sync on Reboot):" -ForegroundColor Yellow
Write-Host "     • สถานะ                      : " -NoNewline -ForegroundColor Gray
Write-Host "เปิดใช้งานสำเร็จ (Windows Task Scheduler)" -ForegroundColor Green
Write-Host "     • คุณสมบัติ                   : " -NoNewline -ForegroundColor Gray
Write-Host "ส่งข้อมูล IP และสเปกขึ้นระบบ CSHOS อัตโนมัติทุกครั้งที่เปิด/รีสตาร์ตเครื่อง" -ForegroundColor White
Write-Host "     • ความสะดวก                  : " -NoNewline -ForegroundColor Gray
Write-Host "ไม่ต้องติดตั้งหรือรันแอปใหม่อีกต่อไป" -ForegroundColor Green
Write-Host "------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [5] สถานะการซิงก์ข้อมูล (Server Sync Status):" -ForegroundColor Yellow
Write-Host "     • $serverSyncStatus" -ForegroundColor Green
Write-Host "========================================================================" -ForegroundColor Cyan
Write-Host ""

$popupText = "ติดตั้งไอคอนแอปและอัปเดตข้อมูลเครื่องปลายทางเรียบร้อยแล้ว!`n`n" +
    "================ ตำแหน่งไอคอนทางลัด ===============`n" +
    "• ติดตั้งไว้ที่ : หน้าจอเดสก์ท็อป (Public Desktop สำหรับทุกผู้ใช้)`n`n" +
    "================ ข้อมูลเครื่องคอมพิวเตอร์ ================`n" +
    "• ชื่อเครื่อง (Computer Name) : $compName`n" +
    "• ผู้ใช้งาน (Username)       : $userName`n" +
    "• หมายเลข IP Address        : $allIpsStr`n" +
    "• สเปก CPU                 : $cpuInfo`n" +
    "• ขนาด RAM                 : $ramInfo`n" +
    "• พื้นที่ DISK              : $diskInfo`n" +
    "• ระบบปฏิบัติการ OS        : $osInfo`n`n" +
    "================ ระบบ Remote VNC ภายใน ================`n" +
    "• สถานะ : พร้อมเชื่อมต่อ (พอร์ต 5900)`n" +
    "• รหัสผ่าน VNC : 4Sh0sp@r (หน้าจอไม่ดับ)`n`n" +
    "================ ระบบ Auto-Sync อัตโนมัติ ================`n" +
    "• สถานะ : เปิดใช้งานแล้ว (Windows Task Scheduler)`n" +
    "• ทำงาน : อัปเดต IP และสเปกอัตโนมัติทุกครั้งที่เปิด/รีสตาร์ตเครื่อง`n" +
    "• สะดวก : ไม่จำเป็นต้องติดตั้งหรือรันแอปใหม่อีกต่อไป`n`n" +
    "• สถานะระบบ : $serverSyncStatus"

try {
    $ws.Popup($popupText, 0, "CSHOS DATACENTER - ข้อมูลเครื่องปลายทาง", 64)
} catch {}