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
        SessionManager $sessionManager,
        private SystemLogService $systemLogService
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
            throw new \DomainException(
                'Email already exists',
                409
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


        $actor = $this->getCurrentUser();

        if ($actor === null || $actor->getRole() !== 'super_admin') {
            throw new \DomainException('Access denied', 403);
        }

        $this->entityManager->wrapInTransaction(
            function () use ($user, $actor): void {
                $this->entityManager->persist($user);

                $actor = $this->getCurrentUser();

                if ($actor === null || $actor->getRole() !== 'super_admin') {
                    throw new \DomainException('Access denied', 403);
                }

                $this->systemLogService->log(
                    $actor,
                    'USER_UPDATED',
                    'User',
                    $user->getId(),
                    'User account updated'
                );

                $this->entityManager->flush();

                $this->systemLogService->log(
                    $actor,
                    'USER_CREATED',
                    'User',
                    $user->getId(),
                    'User account created'
                );
            }
        );

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


    public function listUsers(): array
    {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findBy(
                ['role' => ['student', 'operator']],
                ['id' => 'ASC']
            );

        $result = [];

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'created_at' => $user->getCreatedAt()->format(DATE_ATOM),
            ];
        }

        return $result;
    }

    public function updateUser(int $id, array $data): array
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($id);

        if ($user === null) {
            throw new \DomainException('User not found', 404);
        }

        if (!in_array($user->getRole(), ['student', 'operator'], true)) {
            throw new \DomainException('Access denied', 403);
        }

        if ($data === []) {
            throw new \InvalidArgumentException('No data provided');
        }

        foreach ($data as $field => $value) {
            if (
                !in_array($field, ['name', 'email', 'role', 'password'], true)
                || !is_string($value)
                || trim($value) === ''
            ) {
                throw new \InvalidArgumentException('Invalid user data');
            }
        }

        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);

            if (mb_strlen($data['name']) > 255) {
                throw new \InvalidArgumentException('Invalid name');
            }
        }

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);

            if (
                strlen($data['email']) > 255
                || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
            ) {
                throw new \InvalidArgumentException('Invalid email');
            }

            $existing = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['email' => $data['email']]);

            if ($existing !== null && $existing->getId() !== $id) {
                throw new \InvalidArgumentException('Email already exists');
            }
        }

        if (
            isset($data['role'])
            && !in_array($data['role'], ['student', 'operator'], true)
        ) {
            throw new \InvalidArgumentException('Invalid role');
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        if (isset($data['password'])) {
            $user->setPasswordHash(
                password_hash($data['password'], PASSWORD_DEFAULT)
            );
        }

        $this->entityManager->flush();

        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
        ];
    }

    public function deleteUser(int $id): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($id);

        if ($user === null) {
            throw new \DomainException('User not found', 404);
        }

        if (!in_array($user->getRole(), ['student', 'operator'], true)) {
            throw new \DomainException('Access denied', 403);
        }

        $loanCount = $this->entityManager
            ->getRepository(\Application\Entity\Loan::class)
            ->count(['user' => $user]);

        if ($loanCount > 0) {
            throw new \DomainException(
                'User has borrowing history and cannot be deleted',
                409
            );
        }

        $logCount = $this->entityManager
            ->getRepository(\Application\Entity\SystemLog::class)
            ->count(['actor' => $user]);

        if ($logCount > 0) {
            throw new \DomainException(
                'User has activity history and cannot be deleted',
                409
            );
        }

        $actor = $this->getCurrentUser();

        if ($actor === null || $actor->getRole() !== 'super_admin') {
            throw new \DomainException('Access denied', 403);
        }

        $this->systemLogService->log(
            $actor,
            'USER_DELETED',
            'User',
            $user->getId(),
            'User account deleted'
        );

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
