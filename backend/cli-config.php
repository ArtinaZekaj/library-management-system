
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Laminas\Mvc\Application;

$app = Application::init(
    require __DIR__ . '/config/application.config.php'
);

$connection = $app->getServiceManager()
    ->get('doctrine.connection.orm_default');

$config = new PhpFile(__DIR__ . '/migrations.php');

return DependencyFactory::fromConnection(
    $config,
    new ExistingConnection($connection)
);
