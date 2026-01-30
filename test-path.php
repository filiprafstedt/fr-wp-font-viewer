<?php
echo 'Plugin root: ' . __DIR__ . "<br>";
echo 'Storage folder exists? ' . (is_dir(__DIR__ . '/storage') ? 'YES' : 'NO') . "<br>";
echo 'Font exists? ' . (file_exists(__DIR__ . '/storage/AmarieScript-Regular.woff2') ? 'YES' : 'NO') . "<br>";
