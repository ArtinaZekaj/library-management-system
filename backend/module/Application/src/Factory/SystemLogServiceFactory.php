<?php

namespace Application\Factory;

use Application\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class SystemLogServiceFactory
{
    public function __invoke(
        ContainerInterface $container
    ): SystemLogService {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(
            'doctrine.entitymanager.orm_default'
        );

        return new SystemLogService($entityManager);
    }
}
