<?php

namespace Application\Factory;

use Application\Controller\NotificationController;
use Application\Service\NotificationService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NotificationControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): NotificationController {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $container->get(
            'doctrine.entitymanager.orm_default'
        );

        return new NotificationController(
            new NotificationService($entityManager),
            $container->get(UserService::class)
        );
    }
}
