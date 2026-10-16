$sw = [System.Diagnostics.Stopwatch]::StartNew()

function Step($name) {
    global:sw
    Write-Host "[+$([math]::Round($sw.Elapsed.TotalSeconds, 2))s] $name"
}

Step "Start"

# 1. Hardware specs
Step "Querying CPU"
$cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1

Step "Querying RAM"
$totalRam = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).TotalPhysicalMemory

Step "Querying Disks"
$diskItems = Get-CimInstance Win32_LogicalDisk -Filter "DriveType=3" -ErrorAction SilentlyContinue

Step "Querying Network"
$detectedIps = @(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { 
    $_.InterfaceAlias -notmatch 'Loopback|vEthernet|Virtual|Pseudo' -and 
    $_.IPAddress -notmatch '^169\.254\.' -and 
    $_.IPAddress -ne '127.0.0.1' 
} | Select-Object -ExpandProperty IPAddress)

Step "Checking VNC Service"
$tvnSvc = Get-Service -Name "tvnserver" -ErrorAction SilentlyContinue

Step "Office Detection"
$c2r = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Office\ClickToRun\Configuration" -ErrorAction SilentlyContinue

Step "Network RestMethod"
try {
    $res = Invoke-RestMethod -Uri "http://192.168.1.15/api/track-install" -Method Post -Body (@{device_id="test"}|ConvertTo-Json) -ContentType "application/json" -TimeoutSec 5 -ErrorAction Stop
    Step "RestMethod OK"
} catch {
    Step "RestMethod Failed: $($_.Exception.Message)"
}

Step "Done"

