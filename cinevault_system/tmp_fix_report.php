<?php
$path = __DIR__ . '/app/Http/Controllers/Admin/ReportController.php';
if (!file_exists($path)) {
    echo "MISSING\n";
    exit(1);
}
$contents = file_get_contents($path);
$bytes = substr($contents, 0, 3);
if ($bytes === "\xEF\xBB\xBF") {
    echo "BOM_FOUND\n";
    file_put_contents($path, substr($contents, 3));
    echo "BOM_REMOVED\n";
} else {
    echo "NO_BOM\n";
}
$contents = file_get_contents($path);
$firstLine = strstr($contents, "\n", true);
echo "FIRST_LINE=" . $firstLine . "\n";
echo "LENGTH=" . strlen($contents) . "\n";
?>