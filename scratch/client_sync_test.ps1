
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

$maxWait = 30
$waited = 0
$detectedIps = @()

while ($waited -lt $maxWait) {
    # 1. Prefer active physical interface with Default Gateway route (0.0.0.0/0)
    try {
        $gwRoutes = @(Get-NetRoute -DestinationPrefix '0.0.0.0/0' -AddressFamily IPv4 -ErrorAction SilentlyContinue | Sort-Object RouteMetric)
        foreach ($route in $gwRoutes) {
            $adapter = Get-NetAdapter -InterfaceIndex $route.InterfaceIndex -ErrorAction SilentlyContinue
            if ($adapter) {
                $desc = "$($adapter.InterfaceDescription) $($adapter.Name)"
                if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier') {
                    continue
                }
                $ipObj = Get-NetIPAddress -InterfaceIndex $route.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                         Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -First 1
                if ($ipObj -and $ipObj.IPAddress) {
                    $detectedIps += $ipObj.IPAddress
                }
            }
        }
    } catch {}

    # 2. Physical adapters that are 'Up' without gateway filter
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $upAdapters = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { 
                $_.Status -eq 'Up' -and 
                $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN|WireGuard|Tailscale|ZeroTier' -and
                $_.Name -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth'
            }
            foreach ($ad in $upAdapters) {
                $ips = @(Get-NetIPAddress -InterfaceIndex $ad.InterfaceIndex -AddressFamily IPv4 -ErrorAction SilentlyContinue | 
                         Where-Object { $_.IPAddress -notmatch '^(169\.254\.|127\.)' } | Select-Object -ExpandProperty IPAddress)
                $detectedIps += $ips
            }
        } catch {}
    }

    # 3. Fallback via Win32_NetworkAdapterConfiguration (excluding virtual)
    if (-not $detectedIps -or $detectedIps.Count -eq 0) {
        try {
            $netConfigs = Get-CimInstance Win32_NetworkAdapterConfiguration -Filter "IPEnabled=TRUE" -ErrorAction SilentlyContinue
            foreach ($nc in $netConfigs) {
                $desc = "$($nc.Description)"
                if ($desc -match 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN') {
                    continue
                }
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

# Detect Physical Network MAC Address for reliable DHCP identification
$macAddress = ""
try {
    # Method 1: Check adapter linked to current default gateway / active IP
    if ($gwRoutes -and $gwRoutes.Count -gt 0) {
        $primaryAd = Get-NetAdapter -InterfaceIndex $gwRoutes[0].InterfaceIndex -ErrorAction SilentlyContinue
        if ($primaryAd -and $primaryAd.MacAddress) {
            $macAddress = ($primaryAd.MacAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
    # Method 2: Check any active physical network adapter with MAC
    if (-not $macAddress) {
        $physAd = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object {
            $_.Status -eq 'Up' -and
            $_.InterfaceDescription -notmatch 'VMware|VirtualBox|Virtual|vEthernet|Hyper-V|WSL|Loopback|Bluetooth|Npcap|TAP|VPN' -and
            $_.MacAddress
        } | Select-Object -First 1
        if ($physAd -and $physAd.MacAddress) {
            $macAddress = ($physAd.MacAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
    # Method 3: Fallback via CIM/WMI
    if (-not $macAddress) {
        $cimAd = Get-CimInstance Win32_NetworkAdapter -Filter "PhysicalAdapter=True" -ErrorAction SilentlyContinue |
                 Where-Object { $_.MACAddress -and $_.NetConnectionStatus -eq 2 } | Select-Object -First 1
        if ($cimAd -and $cimAd.MACAddress) {
            $macAddress = ($cimAd.MACAddress -replace '[:\.\s]', '-').ToUpper().Trim()
        }
    }
} catch {}

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

$versionFile = Join-Path $sysDir "version.txt"
$localVersion = "1.0.0"
if (Test-Path $versionFile) {
    try { $localVersion = (Get-Content $versionFile -Raw).Trim() } catch {}
}

$compName = $env:COMPUTERNAME
$userName = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
if (-not $userName) { $userName = $env:USERNAME }
if (-not $userName) { $userName = "System" }

$osInfo = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption
if (-not $osInfo) { $osInfo = [System.Environment]::OSVersion.VersionString }

$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $cpuObj) {
    $cpuObj = Get-WmiObject Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
}
if ($cpuObj -and $cpuObj.Name) {
    $rawName = ($cpuObj.Name -replace '\s+', ' ').Trim()
    $clock = 0
    if ($cpuObj.MaxClockSpeed) { $clock = [int]$cpuObj.MaxClockSpeed }
    elseif ($cpuObj.CurrentClockSpeed) { $clock = [int]$cpuObj.CurrentClockSpeed }
    
    $cores = if ($cpuObj.NumberOfCores) { [int]$cpuObj.NumberOfCores } else { 0 }
    $threads = if ($cpuObj.NumberOfLogicalProcessors) { [int]$cpuObj.NumberOfLogicalProcessors } else { 0 }

    $speedStr = ""
    if ($clock -gt 0 -and $rawName -notmatch '@\s*[0-9\.]+\s*GHz') {
        $ghz = [math]::Round(($clock / 1000), 2)
        $speedStr = "@ $ghz GHz"
    }

    $coreStr = ""
    if ($cores -gt 0 -and $threads -gt 0) {
        $coreStr = "($cores Cores, $threads Threads)"
    } elseif ($cores -gt 0) {
        $coreStr = "($cores Cores)"
    }

    $cpuParts = @($rawName)
    if ($speedStr) { $cpuParts += $speedStr }
    if ($coreStr) { $cpuParts += $coreStr }
    $cpuInfo = $cpuParts -join " "
} else {
    $cpuInfo = "ไม่สามารถระบุ CPU"
}

# RAM Specs & Bus Speed
try {
    $memSticks = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    if (-not $memSticks -or $memSticks.Count -eq 0) {
        $memSticks = @(Get-WmiObject Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    }

    $totalRamBytes = 0
    if ($memSticks -and $memSticks.Count -gt 0) {
        try {
            $sumVal = ($memSticks | Measure-Object -Property Capacity -Sum).Sum
            if ($sumVal -and $sumVal -gt 0) { $totalRamBytes = [double]$sumVal }
        } catch {}
    }

    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $cs = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
            if (-not $cs) { $cs = Get-WmiObject Win32_ComputerSystem -ErrorAction SilentlyContinue }
            if ($cs -and $cs.TotalPhysicalMemory) { $totalRamBytes = [double]$cs.TotalPhysicalMemory }
        } catch {}
    }

    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            Add-Type -AssemblyName Microsoft.VisualBasic -ErrorAction SilentlyContinue
            $ci = New-Object Microsoft.VisualBasic.Devices.ComputerInfo -ErrorAction SilentlyContinue
            if ($ci -and $ci.TotalPhysicalMemory) { $totalRamBytes = [double]$ci.TotalPhysicalMemory }
        } catch {}
    }

    if (-not $totalRamBytes -or $totalRamBytes -le 0) {
        try {
            $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
            if (-not $os) { $os = Get-WmiObject Win32_OperatingSystem -ErrorAction SilentlyContinue }
            if ($os -and $os.TotalVisibleMemorySize) { $totalRamBytes = [double]$os.TotalVisibleMemorySize * 1KB }
        } catch {}
    }

    $ramGB = if ($totalRamBytes -gt 0) { [math]::Round(($totalRamBytes / 1GB), 1) } else { 0 }
    $ramInfo = if ($ramGB -gt 0) { "$ramGB GB" } else { "ไม่สามารถระบุ RAM" }

    if ($memSticks -and $memSticks.Count -gt 0) {
        $first = $memSticks[0]
        $clock = 0
        if ($first.ConfiguredClockSpeed) { $clock = [int]$first.ConfiguredClockSpeed }
        elseif ($first.Speed) { $clock = [int]$first.Speed }

        $smb = if ($first.SMBIOSMemoryType) { [int]$first.SMBIOSMemoryType } else { 0 }
        $memType = switch ($smb) {
            20 { "DDR" }
            21 { "DDR2" }
            24 { "DDR3" }
            26 { "DDR4" }
            34 { "DDR5" }
            default {
                if ($clock -ge 4800) { "DDR5" }
                elseif ($clock -ge 2133) { "DDR4" }
                elseif ($clock -ge 800) { "DDR3" }
                else { "" }
            }
        }
        $busStr = if ($clock -gt 0) { "Bus $clock MHz" } else { "" }
        $part = if ($first.PartNumber) { ($first.PartNumber -as [string]).Trim() } else { "" }
        $brand = if ($first.Manufacturer) { ($first.Manufacturer -as [string]).Trim() } else { "" }
        if ($brand -match '^(Undefined|None|0x[0-9A-Fa-f]+)$') { $brand = "" }
        
        $modelInfo = if ($brand -and $part) { "$brand $part" } elseif ($brand) { "$brand" } elseif ($part) { "$part" } else { "" }
        $stickCount = $memSticks.Count
        $descList = New-Object System.Collections.Generic.List[string]
        if ($memType) { $descList.Add($memType) }
        if ($busStr) { $descList.Add($busStr) }
        if ($stickCount -gt 1) {
            $detailStr = if ($modelInfo) { " - $modelInfo" } else { "" }
            $descList.Add("($stickCount ช่อง$detailStr)")
        } elseif ($modelInfo) {
            $descList.Add("($modelInfo)")
        }
        $details = $descList -join " "
        if ($ramGB -gt 0) {
            if ($details) { $ramInfo = "$ramGB GB $details" } else { $ramInfo = "$ramGB GB" }
        } elseif ($details) {
            $ramInfo = $details
        }
    }
} catch {
    $ramInfo = "ไม่สามารถระบุ RAM"
}

# Harddisk Type (SSD, NVMe M.2, SATA, HDD) & Capacity
try {
    $physDict = @{}
    try {
        Get-PhysicalDisk -ErrorAction SilentlyContinue | ForEach-Object {
            $key = $_.FriendlyName
            $mt = if ($_.MediaType) { $_.MediaType } else { "" }
            $bt = if ($_.BusType) { $_.BusType } else { "" }
            $physDict[$key] = @{ Media = $mt; Bus = $bt }
        }
    } catch {}

    $diskMap = @{}
    try {
        Get-Disk -ErrorAction SilentlyContinue | ForEach-Object {
            $d = $_
            $bt = if ($d.BusType) { $d.BusType } else { "" }
            $mt = if ($physDict.ContainsKey($d.FriendlyName)) { $physDict[$d.FriendlyName].Media } else { "" }
            if (-not $bt -and $physDict.ContainsKey($d.FriendlyName)) { $bt = $physDict[$d.FriendlyName].Bus }
            
            $typeLabel = if ($bt -match 'NVMe') { "SSD NVMe M.2" }
                         elseif ($mt -eq "SSD" -and $bt) { "SSD $bt" }
                         elseif ($mt -eq "SSD") { "SSD" }
                         elseif ($mt -eq "HDD" -and $bt) { "HDD $bt" }
                         elseif ($mt -eq "HDD") { "HDD" }
                         elseif ($bt) { "$bt" }
                         else { "" }
                         
            Get-Partition -DiskNumber $d.Number -ErrorAction SilentlyContinue | Where-Object { $_.DriveLetter } | ForEach-Object {
                $ltr = "$($_.DriveLetter):"
                $diskMap[$ltr] = $typeLabel
            }
        }
    } catch {}

    $diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue | ForEach-Object {
        $dId = $_.DeviceID
        $free = [math]::Round($_.FreeSpace / 1GB, 1)
        $total = [math]::Round($_.Size / 1GB, 1)
        $type = if ($diskMap.ContainsKey($dId) -and $diskMap[$dId]) { " (" + $diskMap[$dId] + ")" } else { "" }
        "$dId$type ว่าง $free/$total GB"
    }
    $diskInfo = if ($diskItems) { $diskItems -join ", " } else { "N/A" }
} catch {
    $diskInfo = "N/A"
}

# Remote VNC Status & Self-Healing
$remoteReady = $false
try {
    $tvn = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
    if ($tvn) {
        if ($tvn.Status -ne 'Running') {
            Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
            Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
            Start-Sleep -Milliseconds 500
            $tvn = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
        }
        if ($tvn -and $tvn.Status -eq 'Running') {
            $remoteReady = $true
        }
    } else {
        # Check if TightVNC is installed in Program Files but service not registered
        $tvnExe = "C:\Program Files\TightVNC\tvnserver.exe"
        if (-not (Test-Path $tvnExe)) { $tvnExe = "C:\Program Files (x86)\TightVNC\tvnserver.exe" }
        if (Test-Path $tvnExe) {
            & "$tvnExe" -install -silent 2>&1 | Out-Null
            Set-Service -Name "tvnserver" -StartupType Automatic -ErrorAction SilentlyContinue
            Start-Service -Name "tvnserver" -ErrorAction SilentlyContinue
            $tvn = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue
            if ($tvn -and $tvn.Status -eq 'Running') { $remoteReady = $true }
        }
    }

    # Fallback 1: Check if Port 5900 is actively listening locally (TCP Listen)
    if (-not $remoteReady) {
        try {
            $tcp5900 = Get-NetTCPConnection -LocalPort 5900 -State Listen -ErrorAction SilentlyContinue
            if ($tcp5900) { $remoteReady = $true }
        } catch {}
    }

    # Fallback 2: Check if tvnserver process is active in user session / tray mode
    if (-not $remoteReady) {
        try {
            $procTvn = Get-Process -Name "tvnserver" -ErrorAction SilentlyContinue
            if ($procTvn) { $remoteReady = $true }
        } catch {}
    }

    # Ensure Windows Firewall rule allows Port 5900 for all network profiles
    if ($remoteReady) {
        netsh advfirewall firewall add rule name="TightVNC Server Port 5900" dir=in action=allow protocol=TCP localport=5900 profile=any | Out-Null
    }
} catch {}

# Detect OS Version
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

# Detect Microsoft Office Version
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

# Detect OS Last Boot Up Time & System Uptime
$lastBootStr = ""
$uptimeFormatted = ""
try {
    $osCim = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
    if ($osCim -and $osCim.LastBootUpTime) {
        $lastBootStr = $osCim.LastBootUpTime.ToString('yyyy-MM-dd HH:mm:ss')
        $upSpan = (Get-Date) - $osCim.LastBootUpTime
        $d = [math]::Floor($upSpan.TotalDays)
        $h = $upSpan.Hours
        $m = $upSpan.Minutes
        $parts = @()
        if ($d -gt 0) { $parts += "$d วัน" }
        if ($h -gt 0) { $parts += "$h ชม." }
        if ($m -gt 0 -or $parts.Count -eq 0) { $parts += "$m นาที" }
        $uptimeFormatted = $parts -join " "
    }
} catch {}

# Detect AnyDesk Installation & AnyDesk ID (Multi-path & Multi-method Detection)
$anydeskFound = $false
$anydeskIdStr = ""
$anydeskExe = ""
try {
    # 1. Running Process Detection
    $adProc = Get-Process -Name "AnyDesk" -ErrorAction SilentlyContinue
    if ($adProc) {
        $anydeskFound = $true
        foreach ($pr in $adProc) {
            if ($pr.Path -and (Test-Path $pr.Path)) {
                $anydeskExe = $pr.Path
                break
            }
        }
    }

    # 2. Windows Service Detection (AnyDesk Service)
    if (-not $anydeskExe) {
        $adSvc = Get-Service -Name "AnyDesk" -ErrorAction SilentlyContinue
        if ($adSvc) {
            $anydeskFound = $true
            try {
                $cimSvc = Get-CimInstance Win32_Service -Filter "Name='AnyDesk'" -ErrorAction SilentlyContinue
                if ($cimSvc -and $cimSvc.PathName) {
                    $sClean = $cimSvc.PathName -replace '^\s*"([^"]+)".*$', '$1' -replace '^\s*([^\s]+).*$', '$1'
                    if (Test-Path $sClean) { $anydeskExe = $sClean }
                }
            } catch {}
        }
    }

    # 3. Registry App Paths (HKLM 64-bit, HKLM 32-bit WOW64, HKCU)
    if (-not $anydeskExe) {
        $regAppPaths = @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\AnyDesk.exe"
        )
        foreach ($rap in $regAppPaths) {
            $apVal = (Get-ItemProperty $rap -ErrorAction SilentlyContinue).'(Default)'
            if ($apVal -and (Test-Path $apVal)) {
                $anydeskExe = $apVal
                $anydeskFound = $true
                break
            }
        }
    }

    # 4. Registry URL Protocol Handler (anydesk:// command)
    if (-not $anydeskExe) {
        $protoKeys = @(
            "HKLM:\SOFTWARE\Classes\anydesk\shell\open\command",
            "HKCU:\SOFTWARE\Classes\anydesk\shell\open\command"
        )
        foreach ($pk in $protoKeys) {
            $cmdVal = (Get-ItemProperty $pk -ErrorAction SilentlyContinue).'(Default)'
            if ($cmdVal) {
                $m = [regex]::Match($cmdVal, '(?i)"?([a-zA-Z]:\\[^"]+?anydesk\.exe)"?')
                if ($m.Success -and (Test-Path $m.Groups[1].Value)) {
                    $anydeskExe = $m.Groups[1].Value
                    $anydeskFound = $true
                    break
                }
            }
        }
    }

    # 5. Registry Uninstall Entries
    if (-not $anydeskFound -or -not $anydeskExe) {
        $adReg = Get-ItemProperty @(
            "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*",
            "HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*"
        ) -ErrorAction SilentlyContinue | Where-Object { $_.DisplayName -match 'AnyDesk' }
        if ($adReg) {
            $anydeskFound = $true
            foreach ($u in $adReg) {
                if ($u.DisplayIcon) {
                    $iconExe = $u.DisplayIcon -replace ',.*$', '' -replace '"', ''
                    if (Test-Path $iconExe) { $anydeskExe = $iconExe; break }
                }
                if ($u.InstallLocation) {
                    $cand = Join-Path $u.InstallLocation "AnyDesk.exe"
                    if (Test-Path $cand) { $anydeskExe = $cand; break }
                }
            }
        }
    }

    # 6. Comprehensive File Path Check across All Drives (C:, D:, etc.)
    if (-not $anydeskExe) {
        $drives = @("C:")
        try {
            $allDrives = @(Get-PSDrive -PSProvider FileSystem -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Root)
            if ($allDrives -and $allDrives.Count -gt 0) { $drives = $allDrives }
        } catch {}

        $candRel = @(
            "Program Files (x86)\AnyDesk\AnyDesk.exe",
            "Program Files\AnyDesk\AnyDesk.exe",
            "AnyDesk\AnyDesk.exe",
            "ProgramData\AnyDesk\AnyDesk.exe"
        )
        foreach ($drv in $drives) {
            $cleanDrv = $drv.TrimEnd('\')
            foreach ($rel in $candRel) {
                $fp = "$cleanDrv\$rel"
                if (Test-Path $fp) {
                    $anydeskExe = $fp
                    $anydeskFound = $true
                    break
                }
            }
            if ($anydeskExe) { break }
        }
    }

    # 7. User AppData, Local Programs, and User Profile Searches
    if (-not $anydeskExe) {
        $userAdPaths = @(
            "$env:ProgramData\AnyDesk\AnyDesk.exe",
            "$env:LOCALAPPDATA\Programs\AnyDesk\AnyDesk.exe",
            "$env:LOCALAPPDATA\AnyDesk\AnyDesk.exe",
            "$env:APPDATA\AnyDesk\AnyDesk.exe"
        )
        foreach ($uap in $userAdPaths) {
            if ($uap -and (Test-Path $uap)) {
                $anydeskExe = $uap
                $anydeskFound = $true
                break
            }
        }
    }
    if (-not $anydeskExe) {
        $foundExes = @(Get-ChildItem -Path "C:\Users\*\AppData\Local\Programs\AnyDesk\AnyDesk.exe", "C:\Users\*\AppData\Roaming\AnyDesk\AnyDesk.exe" -ErrorAction SilentlyContinue)
        if ($foundExes -and $foundExes.Count -gt 0) {
            $anydeskExe = $foundExes[0].FullName
            $anydeskFound = $true
        }
    }

    # 8. Extract AnyDesk ID
    # Method A: Query AnyDesk CLI directly (Fastest, 100% accurate regardless of path)
    if ($anydeskExe -and (Test-Path $anydeskExe)) {
        try {
            $rawCli = (cmd /c "`"$anydeskExe`" --get-id" 2>&1 | Out-String).Trim()
            if ($rawCli -match '^\d{9,10}$') {
                $rawId = $rawCli
                if ($rawId.Length -eq 9) {
                    $anydeskIdStr = $rawId.Substring(0,3) + " " + $rawId.Substring(3,3) + " " + $rawId.Substring(6,3)
                } elseif ($rawId.Length -eq 10) {
                    $anydeskIdStr = $rawId.Substring(0,4) + " " + $rawId.Substring(4,3) + " " + $rawId.Substring(7,3)
                } else {
                    $anydeskIdStr = $rawId
                }
                $anydeskFound = $true
            }
        } catch {}
    }

    # Method B: Search and parse system.conf and user.conf across system and all user profiles
    if (-not $anydeskIdStr) {
        $confFiles = New-Object System.Collections.Generic.List[string]
        $knownConfs = @(
            "$env:ProgramData\AnyDesk\system.conf",
            "$env:ALLUSERSPROFILE\AnyDesk\system.conf",
            "C:\ProgramData\AnyDesk\system.conf",
            "C:\ProgramData\AnyDesk\user.conf",
            "C:\Windows\SysWOW64\config\systemprofile\AppData\Roaming\AnyDesk\system.conf",
            "C:\Windows\System32\config\systemprofile\AppData\Roaming\AnyDesk\system.conf",
            "$env:APPDATA\AnyDesk\user.conf",
            "$env:APPDATA\AnyDesk\system.conf"
        )
        foreach ($kc in $knownConfs) {
            if ($kc -and (Test-Path $kc) -and -not $confFiles.Contains($kc)) {
                $confFiles.Add($kc)
            }
        }

        try {
            $userConfs = @(Get-ChildItem -Path "C:\Users\*\AppData\Roaming\AnyDesk" -Filter "*.conf" -ErrorAction SilentlyContinue)
            foreach ($uc in $userConfs) {
                if (-not $confFiles.Contains($uc.FullName)) {
                    $confFiles.Add($uc.FullName)
                }
            }
        } catch {}

        foreach ($cp in $confFiles) {
            $confText = Get-Content -Path $cp -ErrorAction SilentlyContinue | Out-String
            if ($confText -match 'ad\.anynet\.id\s*=\s*(\d+)') {
                $rawId = $matches[1].Trim()
                if ($rawId.Length -eq 9) {
                    $anydeskIdStr = $rawId.Substring(0,3) + " " + $rawId.Substring(3,3) + " " + $rawId.Substring(6,3)
                } elseif ($rawId.Length -eq 10) {
                    $anydeskIdStr = $rawId.Substring(0,4) + " " + $rawId.Substring(4,3) + " " + $rawId.Substring(7,3)
                } else {
                    $anydeskIdStr = $rawId
                }
                $anydeskFound = $true
                break
            }
        }
    }
} catch {}

$reportObj = @{
    device_id         = $devId
    hostname          = $compName
    device_name       = $userName
    ip_address        = $clientIp
    mac_address       = $macAddress
    os                = $osInfo
    os_version        = $osVerStr
    office_version    = $officeFinal
    cpu               = $cpuInfo
    ram               = $ramInfo
    disk              = $diskInfo
    remote_ready      = $remoteReady
    last_boot_time    = $lastBootStr
    uptime_text       = $uptimeFormatted
    anydesk_installed = $anydeskFound
    anydesk_id        = $anydeskIdStr
    install_type      = 'installed'
    browser           = 'AutoSync Service'
    client_version    = $localVersion
}
$jsonPayload = $reportObj | ConvertTo-Json

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11 -bor [Net.SecurityProtocolType]::Tls
    $res = Invoke-RestMethod -Uri "http://localhost/api/track-install" -Method Post -Body $jsonPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 10 -ErrorAction Stop
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - OK: Host=$compName, IP=$clientIp, RAM=$ramInfo, DISK=$diskInfo, Ver=$localVersion"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue

    # Auto-Update: Check if server has released a newer version or commanded an update
    $serverVer = if ($res -and $res.server_version) { "$($res.server_version)".Trim() } else { "" }
    $isUpdateNeeded = $false
    if ($serverVer -and ($serverVer -ne $localVersion)) { $isUpdateNeeded = $true }
    if ($res -and ($res.force_update -or $res.update_available)) { $isUpdateNeeded = $true }

    if ($isUpdateNeeded) {
        $logUpd = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Server update available: $serverVer (Current: $localVersion, Force=$($res.force_update)). Starting auto-update..."
        Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logUpd -ErrorAction SilentlyContinue
        try {
            $updUrl = if ($res.update_script_url) { $res.update_script_url } else { "http://localhost/api/client/update-script" }
            $updCode = (Invoke-RestMethod -Uri $updUrl -UseBasicParsing -TimeoutSec 15 -ErrorAction Stop)
            if ($updCode -and $updCode.Length -gt 50) {
                $tmpUpd = Join-Path $env:TEMP "cshos_upd_$([guid]::NewGuid().ToString('N')).ps1"
                Set-Content -Path $tmpUpd -Value $updCode -Encoding UTF8 -Force
                & powershell.exe -NoProfile -ExecutionPolicy Bypass -File $tmpUpd
                Remove-Item -Path $tmpUpd -Force -ErrorAction SilentlyContinue
                Set-Content -Path $versionFile -Value $serverVer -Force
                Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Auto-Update completed successfully to version $serverVer." -ErrorAction SilentlyContinue

                # Send immediate confirmation check-in to server to clear pending_update
                try {
                    $reportObj.client_version = $serverVer
                    $confirmPayload = $reportObj | ConvertTo-Json
                    Invoke-RestMethod -Uri "http://localhost/api/track-install" -Method Post -Body $confirmPayload -ContentType "application/json; charset=utf-8" -TimeoutSec 10 -ErrorAction SilentlyContinue | Out-Null
                } catch {}
            }
        } catch {
            Add-Content -Path (Join-Path $sysDir "sync.log") -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Auto-Update failed: $($_.Exception.Message)" -ErrorAction SilentlyContinue
        }
    }

    # Ensure Auto-Start shortcut in Startup folder is intact
    $commonStartup = [Environment]::GetFolderPath('CommonStartup')
    if (-not $commonStartup -or -not (Test-Path $commonStartup)) { $commonStartup = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Startup" }
    $startSc = Join-Path $commonStartup "CSHOS STAFF INFO.lnk"
    if (-not (Test-Path $startSc)) {
        try {
            $ws = New-Object -ComObject WScript.Shell
            $scStart = $ws.CreateShortcut($startSc)
            $browserCandidates = @('C:\Program Files\Google\Chrome\Application\chrome.exe','C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',"$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe",'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe','C:\Program Files\Microsoft\Edge\Application\msedge.exe')
            $bExe = ""
            foreach ($c in $browserCandidates) { if (Test-Path $c) { $bExe = $c; break } }
            if ($bExe) {
                $scStart.TargetPath = $bExe
                $scStart.Arguments = "--app=`"http://localhost`""
            } else {
                $scStart.TargetPath = 'http://localhost'
            }
            $scStart.WorkingDirectory = $sysDir
            $scStart.Description = "CSHOS STAFF INFO - เปิดโปรแกรมอัตโนมัติเมื่อเปิดเครื่อง (Auto-Start)"
            $ico = Join-Path $sysDir "app-icon.ico"
            if (Test-Path $ico) { $scStart.IconLocation = "$ico,0" }
            $scStart.Save()
        } catch {}
    }
} catch {
    $logMsg = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Err: $($_.Exception.Message)"
    Add-Content -Path (Join-Path $sysDir "sync.log") -Value $logMsg -ErrorAction SilentlyContinue
}