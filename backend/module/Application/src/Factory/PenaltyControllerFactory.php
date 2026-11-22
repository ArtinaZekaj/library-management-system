<?php

namespace Application\Factory;

use Application\Controller\PenaltyController;
use Application\Service\PenaltyService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PenaltyControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): PenaltyController {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $container->get(
            'doctrine.entitymanager.orm_default'
        );

        return new PenaltyController(
            new PenaltyService($entityManager),
            $container->get(UserService::class)
        );
    }
}
