<?php
echo "Current directory: " . __DIR__ . "<br>";
echo "Files in this directory:<br>";
$files = scandir(__DIR__);
foreach($files as $file) {
    echo "- " . $file . "<br>";
}
?>
