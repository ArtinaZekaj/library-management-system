<?php

namespace Application\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Application\Controller\AuthController;
use Application\Service\UserService;
use Application\Service\RbacService;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthController(
            $container->get(UserService::class),
            $container->get(RbacService::class)
        );
    }
}
