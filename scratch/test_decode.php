<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$req = Illuminate\Http\Request::capture();
$c = new App\Http\Controllers\AppInstallationController();
$resp = $c->downloadShortcutInstaller($req);
file_put_contents('scratch/test_installer.bat', $resp->getContent());

// Decode using certutil
shell_exec('certutil -decode scratch/test_installer.bat scratch/test_decoded.ps1 >nul 2>&1');
$psContent = file_get_contents('scratch/test_decoded.ps1');
echo "Decoded length: " . strlen($psContent) . "\n";
echo "Has UTF-8 BOM: " . (substr($psContent, 0, 3) === "\xEF\xBB\xBF" ? "YES" : "NO") . "\n";

