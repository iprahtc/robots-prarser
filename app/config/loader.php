<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir
    ]
);
$loader->registerClasses([
    'RobotsTxtParser' => APP_PATH . '/library/RobotsTxtParser.php'
]);
$loader->register();