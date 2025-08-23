<?php
require 'vendor/autoload.php';

use HTMLPurifier;
use HTMLPurifier_Config;

$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', '');
$cachePath = 'storage/app/purifier';
if (!is_dir($cachePath)) {
    mkdir($cachePath, 0755, true);
}
$config->set('Cache.SerializerPath', $cachePath);

if (!is_writable($cachePath)) {
    $config->set('Cache.DefinitionImpl', null);
}

$purifier = new HTMLPurifier($config);
$clean = $purifier->purify('<script>alert(1)</script>Hello World');
echo 'Input: <script>alert(1)</script>Hello World' . PHP_EOL;
echo 'Output: ' . $clean . PHP_EOL;
echo 'HTMLPurifier working correctly!' . PHP_EOL;
