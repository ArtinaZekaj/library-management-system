<?php

namespace Application\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Application\Service\AuthorizationService;
use Application\Service\UserService;
use Application\Service\RbacService;

class AuthorizationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthorizationService(
            $container->get(UserService::class),
            $container->get(RbacService::class)
        );
    }
}

