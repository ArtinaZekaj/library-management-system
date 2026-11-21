<?php

namespace Application\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Application\Service\UserService;
use Laminas\Session\SessionManager;
use Doctrine\ORM\EntityManagerInterface;

class UserServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManagerInterface $entityManager */

        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $sessionManager = $container->get(SessionManager::class);

        return new UserService($entityManager, $sessionManager);
    }
}
