<?php

namespace Application\Factory;

use Application\Controller\SettingsController;
use Application\Service\SettingsService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SettingsControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): SettingsController {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $container->get(
            'doctrine.entitymanager.orm_default'
        );

        return new SettingsController(
            new SettingsService($entityManager),
            $container->get(UserService::class)
        );
    }
}
