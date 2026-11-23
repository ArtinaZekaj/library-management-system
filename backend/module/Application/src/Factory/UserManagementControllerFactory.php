<?php

namespace Application\Factory;

use Application\Controller\UserManagementController;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UserManagementControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): UserManagementController {
        return new UserManagementController(
            $container->get(UserService::class)
        );
    }
}
