<?php

chdir(dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(__DIR__));

$loader = BASE_PATH . DS . 'backend' . DS . 'Framework' . DS . 'Core' . DS . 'Loader' .DS  . 'SplClassLoader.php';
$framework = BASE_PATH . DS . 'backend' . DS . 'Framework' . DS . 'Safan.php';

require_once($loader);
require_once($framework);

$loader = new Framework\Core\Loader\SplClassLoader('Framework', 'backend');
$loader->register();

Framework\Safan::createApp('\Framework\Core\Application\WebApplication')->run();

