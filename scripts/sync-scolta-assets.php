<?php
// Prefer the monorepo source (development) over the installed vendor copy.
$base = __DIR__ . '/../../../packages/scolta-php/assets';
$vendorBase = __DIR__ . '/../vendor/tag1/scolta-php/assets';

$jsSrc = file_exists("$base/js/scolta.js") ? "$base/js/scolta.js"
    : (file_exists("$vendorBase/js/scolta.js") ? "$vendorBase/js/scolta.js" : null);

$cssSrc = file_exists("$base/css/scolta.css") ? "$base/css/scolta.css"
    : (file_exists("$vendorBase/css/scolta.css") ? "$vendorBase/css/scolta.css" : null);

if ($jsSrc === null) {
    fwrite(STDERR, "sync-scolta-assets: scolta.js not found\n");
    exit(1);
}

foreach (glob(__DIR__ . '/../web/modules/contrib/scolta*/js') as $dir) {
    copy($jsSrc, $dir . '/scolta.js');
}

if ($cssSrc !== null) {
    foreach (glob(__DIR__ . '/../web/modules/contrib/scolta*/css') as $dir) {
        copy($cssSrc, $dir . '/scolta.css');
    }
}
