<?php

namespace ApplicationTest\Service;

use Laminas\Mvc\Application;
use PHPUnit\Framework\TestCase;
use Application\Service\UserService;
use Application\Entity\User;


class UserServiceTest extends TestCase
{

    protected $serviceManager;

    protected function setUp(): void
    {
        $app = Application::init(
            require 'config/application.config.php'
        );

        $this->serviceManager = $app->getServiceManager();
    }

    public function testUserServiceRetrieved(): void
    {
        $userService = $this->serviceManager->get(UserService::class);

        $this->assertInstanceOf(
            UserService::class,
            $userService
        );
    }

    public function testAddUser(): void
    {
        $userService = $this->serviceManager->get(UserService::class);

        $entityManager = $this->serviceManager->get(
            'doctrine.entitymanager.orm_default'
        );

        $connection = $entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '12345678',
                'role' => 'student'
            ];

            $user = $userService->addUser($data);

            $this->assertInstanceOf(User::class, $user);

            $this->assertSame('Test User', $user->getName());

            $this->assertSame('test@example.com', $user->getEmail());

            $this->assertSame('student', $user->getRole());

            $this->assertTrue(
                password_verify('12345678', $user->getPasswordHash())
            );
        } finally {
            $connection->rollBack();
        }
    }

    public function testDuplicateEmail(): void
    {
        $userService = $this->serviceManager->get(UserService::class);

        $entityManager = $this->serviceManager->get(
            'doctrine.entitymanager.orm_default'
        );

        $connection = $entityManager->getConnection();

        $connection->beginTransaction();

        try {
            $data = [
                'name' => 'Test User',
                'email' => 'testduplicate@example.com',
                'password' => '12345678',
                'role' => 'student'
            ];

            $userService->addUser($data);

            $this->expectException(\Exception::class);

            $userService->addUser($data);
        } finally {
            $connection->rollBack();
        }
    }
}
