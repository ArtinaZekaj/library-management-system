<?php

namespace Application\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Application\Controller\BookController;
use Application\Service\AuthorizationService;
use Doctrine\ORM\EntityManager;

class BookControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new BookController(
            $container->get(AuthorizationService::class),
            $container->get(EntityManager::class)
        );
    }
}