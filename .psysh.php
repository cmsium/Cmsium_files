<?php

$defaultIncludes = [];
$bootstrapPath = __DIR__.'/boot/loader.php';
if (file_exists($bootstrapPath)) {
    $defaultIncludes[] = $bootstrapPath;
}
return [
    'defaultIncludes' => $defaultIncludes,
    'startupMessage' => '<info>File controller app loaded. Have fun!</info>'
];