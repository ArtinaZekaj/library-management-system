<?php

namespace Application\Factory;

use Application\Controller\SystemLogController;
use Application\Service\SystemLogService;
use Application\Service\UserService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SystemLogControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): SystemLogController {
        return new SystemLogController(
            $container->get(SystemLogService::class),
            $container->get(UserService::class)
        );
    }
}
