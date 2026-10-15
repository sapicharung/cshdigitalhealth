@echo off
chcp 65001 >nul
title CSHOS - ตั้งค่าระบบเปิดโปรแกรม VNC อัตโนมัติ (One-Click VNC Launcher)
echo ========================================================================
echo    CSHOS DATACENTER - โรงพยาบาลเชียงแสน
echo    กำลังตั้งค่าระบบเปิดโปรแกรม TightVNC Viewer อัตโนมัติ (URL Protocol)
echo ========================================================================
echo.

if not exist "C:\ProgramData\CSHOS" mkdir "C:\ProgramData\CSHOS" >nul 2>&1

(
echo 4Sh0sp@r
) > "C:\ProgramData\CSHOS\vnc_password.txt"

(
echo Set args = WScript.Arguments
echo If args.Count = 0 Then WScript.Quit
echo rawUrl = Replace^(Replace^(args^(0^), "vnc://", "", 1, -1, 1^), "/", ""^)
echo host = ""
echo port = "5900"
echo pass = "4Sh0sp@r"
echo Set fso = CreateObject^("Scripting.FileSystemObject"^)
echo If fso.FileExists^("C:\ProgramData\CSHOS\vnc_password.txt"^) Then
echo     On Error Resume Next
echo     Set pf = fso.OpenTextFile^("C:\ProgramData\CSHOS\vnc_password.txt", 1^)
echo     pass = Trim^(pf.ReadAll^)
echo     pf.Close
echo     On Error Goto 0
echo End If
echo If Len^(pass^) ^> 8 Then pass = Left^(pass, 8^)
echo targetPart = rawUrl
echo If InStr^(targetPart, ":"^) ^> 0 Then
echo     hParts = Split^(targetPart, ":"^)
echo     host = hParts^(0^)
echo     port = hParts^(1^)
echo Else
echo     host = targetPart
echo End If
echo vncExe = "C:\Program Files\TightVNC\tvnviewer.exe"
echo If Not fso.FileExists^(vncExe^) Then vncExe = "C:\Program Files (x86)\TightVNC\tvnviewer.exe"
echo If Not fso.FileExists^(vncExe^) Then
echo     MsgBox "ไม่พบโปรแกรม TightVNC Viewer (tvnviewer.exe)" ^& vbCrLf ^& "กรุณาติดตั้ง TightVNC ก่อนใช้งาน", 16, "CSHOS VNC"
echo     WScript.Quit
echo End If
echo Set shell = CreateObject^("WScript.Shell"^)
echo If pass ^<^> "" Then
echo     cmd = """" ^& vncExe ^& """ -host=" ^& host ^& " -port=" ^& port ^& " -password=" ^& pass
echo Else
echo     cmd = """" ^& vncExe ^& """ -host=" ^& host ^& " -port=" ^& port
echo End If
echo shell.Run cmd, 1, False
) > "C:\ProgramData\CSHOS\vnc-launcher.vbs"

reg add "HKCU\Software\Classes\vnc" /ve /t REG_SZ /d "URL:VNC Protocol" /f >nul 2>&1
reg add "HKCU\Software\Classes\vnc" /v "URL Protocol" /t REG_SZ /d "" /f >nul 2>&1
reg add "HKCU\Software\Classes\vnc\shell\open\command" /ve /t REG_SZ /d "wscript.exe \"C:\ProgramData\CSHOS\vnc-launcher.vbs\" \"%%1\"" /f >nul 2>&1

echo [SUCCESS] ติดตั้งระบบเรียกโปรแกรม VNC อัตโนมัติสำเร็จเรียบร้อย!
echo           เมื่อคลิกปุ่ม 'Remote' ในหน้าเว็บ CSHOS
echo           ระบบจะเรียกโปรแกรม TightVNC Viewer ขึ้นมาเชื่อมต่อไปยังเครื่องปลายทางทันที
echo.
pause
