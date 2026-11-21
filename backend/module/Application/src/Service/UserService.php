<?php

namespace Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use Application\Entity\User;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;

class UserService
{

    protected EntityManagerInterface $entityManager;
    protected SessionManager $sessionManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        SessionManager $sessionManager
    ) {
        $this->entityManager = $entityManager;
        $this->sessionManager = $sessionManager;
    }

    public function checkUserExists(string $email): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);


        if ($user == null) {
            return false;
        }
        return true;
    }

    public function addUser($data)
    {
        if ($this->checkUserExists($data['email'])) {
            throw new \Exception(
                "User with email address" . $data['email'] . "already exists"
            );
        }

        $user = new User();

        $user->setName($data['name']);
        $user->setEmail($data['email']);

        $passwordHash = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );
        $user->setPasswordHash($passwordHash);

        $user->setRole($data['role']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function login(string $email, string $password)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);


        if ($user == null) {
            return false;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return false;
        }

        Container::setDefaultManager($this->sessionManager);

        $this->sessionManager->start();
        $this->sessionManager->regenerateId(true);

        $session = new Container('user');

        $session->userId = $user->getId();

        return true;
    }

    public function getCurrentUser(): ?User
    {
        Container::setDefaultManager($this->sessionManager);

        $this->sessionManager->start();

        $session = new Container('user');

        if (!isset($session->userId)) {
            return null;
        }

        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $session->userId]);
    }

    public function logout(): void
    {
        $this->sessionManager->start();

        $this->sessionManager->destroy();
    }
}
