<?php

$root = realpath(dirname(__DIR__));

require_once "$root/vendor/autoload.php";

if (file_exists("$root/config.php")) {
    require_once "$root/config.php";
}

$defaults = [
    'WPR_LOG' => 'php://stdout',
    'WPR_CACHE' => "$root/cache",
    'WPR_CONFIG' => "$root/satis.json",
    'WPR_EXPIRE' => 365 * 24 * 60 * 60,
    'WPR_SOURCE' => "$root/artifacts",
    'WPR_TARGET' => "$root/public",
];

foreach ($defaults as $key => $value) {
    defined($key) || define($key, $value);
}

unset($root);
unset($defaults);
unset($key);
unset($value);

(new \WPRepo\Application())->run();
