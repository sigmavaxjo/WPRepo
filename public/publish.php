<?php

$root = realpath(dirname(__DIR__));

require_once "$root/vendor/autoload.php";

define('WPR_CONFIG', "$root/satis.json");
define('WPR_EXPIRE', 365 * 24 * 60 * 60);
define('WPR_SOURCE', "$root/artifacts");
define('WPR_TARGET', "$root/public");

if (file_exists("$root/config.php")) {
    require_once "$root/config.php";
}

unset($root);

(new \WPRepo\Application())->run();
