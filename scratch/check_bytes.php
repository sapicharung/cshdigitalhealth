<?php
$content = file_get_contents('app/Http/Controllers/AppInstallationController.php');
$pos = strpos($content, 'CSHOS DIGITAL HEALTH - ');
if ($pos !== false) {
    $sub = substr($content, $pos, 60);
    echo "Substr: " . $sub . "\n";
    echo "Hex bytes: " . bin2hex($sub) . "\n";
} else {
    echo "Not found\n";
}

