<?php
$content = file_get_contents('app/Http/Controllers/AppInstallationController.php');
if (mb_check_encoding($content, 'UTF-8')) {
    echo "AppInstallationController.php is VALID UTF-8\n";
} else {
    echo "AppInstallationController.php is NOT valid UTF-8!\n";
}

