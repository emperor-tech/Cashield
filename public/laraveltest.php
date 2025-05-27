<?php
echo "Laravel environment test<br>";
echo "PHP version: " . phpversion() . "<br>";
echo "Document root: " . $_SERVER["DOCUMENT_ROOT"] . "<br>";
echo "Script filename: " . $_SERVER["SCRIPT_FILENAME"] . "<br>";
echo "Request URI: " . $_SERVER["REQUEST_URI"] . "<br>";

// Check if Laravel bootstrap files exist
echo "Bootstrap path exists: " . (file_exists(__DIR__ . "/../bootstrap/app.php") ? "Yes" : "No") . "<br>";
echo "Autoload path exists: " . (file_exists(__DIR__ . "/../vendor/autoload.php") ? "Yes" : "No") . "<br>";

// Check storage permissions
echo "Storage directory writable: " . (is_writable(__DIR__ . "/../storage") ? "Yes" : "No") . "<br>";
echo "Bootstrap/cache writable: " . (is_writable(__DIR__ . "/../bootstrap/cache") ? "Yes" : "No") . "<br>";
?>
