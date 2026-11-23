<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Application\Factory\UserServiceFactory;
use Application\Service\UserService;
use Application\Factory\AuthControllerFactory;
use Application\Service\RbacService;
use Application\Service\AuthorizationService;
use Application\Factory\AuthorizationServiceFactory;
use Application\Controller\BookController;
use Application\Factory\BookControllerFactory;
use Application\Controller\LoanController;
use Application\Factory\LoanControllerFactory;
use Application\Controller\PenaltyController;
use Application\Factory\PenaltyControllerFactory;
use Application\Controller\NotificationController;
use Application\Factory\NotificationControllerFactory;
use Application\Controller\SettingsController;
use Application\Factory\SettingsControllerFactory;
use Application\Controller\UserManagementController;
use Application\Factory\UserManagementControllerFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'api-login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'api-me' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/me',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'me',
                    ],
                ],
            ],
            'api-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'api-books' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/books',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-book-detail' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/books/:id',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'view',
                    ],
                ],
            ],
            'api-loans' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/loans',
                    'defaults' => [
                        'controller' => LoanController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-loan-return' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/loans/:id/return',
                    'constraints' => [
                        'id' => '[1-9][0-9]*',
                    ],
                    'defaults' => [
                        'controller' => LoanController::class,
                        'action' => 'return',
                    ],
                ],
            ],
            'api-penalty-pay' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/penalties/:id/pay',
                    'constraints' => [
                        'id' => '[1-9][0-9]*',
                    ],
                    'defaults' => [
                        'controller' => PenaltyController::class,
                        'action' => 'pay',
                    ],
                ],
            ],
            'api-notifications' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/notifications',
                    'defaults' => [
                        'controller' => NotificationController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-settings' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/settings',
                    'defaults' => [
                        'controller' => SettingsController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-users' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/users',
                    'defaults' => [
                        'controller' => UserManagementController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-user-detail' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/users/:id',
                    'constraints' => [
                        'id' => '[1-9][0-9]*',
                    ],
                    'defaults' => [
                        'controller' => UserManagementController::class,
                        'action' => 'detail',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\AuthController::class => AuthControllerFactory::class,
            BookController::class => BookControllerFactory::class,
            LoanController::class => LoanControllerFactory::class,
            PenaltyController::class => PenaltyControllerFactory::class,
            NotificationController::class => NotificationControllerFactory::class,
            SettingsController::class => SettingsControllerFactory::class,
            UserManagementController::class => UserManagementControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            UserService::class => UserServiceFactory::class,
            RbacService::class => InvokableFactory::class,
            AuthorizationService::class => AuthorizationServiceFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'doctrine' => [
        'driver' => [
            'application_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Entity' => 'application_entities',
                ],
            ],
        ],
    ],
];
