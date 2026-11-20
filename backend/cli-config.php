
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Laminas\Mvc\Application;

$app = Application::init(
    require __DIR__ . '/config/application.config.php'
);

$entityManager = $app->getServiceManager()
    ->get('doctrine.entitymanager.orm_default');

$config = new PhpFile(__DIR__ . '/migrations.php');

return DependencyFactory::fromEntityManager(
    $config,
    new ExistingEntityManager($entityManager)
);
