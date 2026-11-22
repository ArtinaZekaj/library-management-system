<?php

namespace Application\Factory;

use Application\Controller\LoanController;
use Application\Service\AuthorizationService;
use Application\Service\LoanService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LoanControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): LoanController {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $container->get(
            'doctrine.entitymanager.orm_default'
        );

        return new LoanController(
            new LoanService(
                $entityManager,
                new \Application\Service\PenaltyService($entityManager)
            ),
            $container->get(UserService::class),
            $container->get(AuthorizationService::class)
        );
    }
}
